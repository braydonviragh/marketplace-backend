<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Transfer;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Support\Facades\Log;
use Stripe\Account;
use Stripe\AccountLink;
use App\Models\Rental;

class StripeService
{
    private bool $isTestMode;

    public function __construct()
    {
        $this->isTestMode = config('services.stripe.test_mode', true);
        
        // Initialize Stripe with your secret key
        Stripe::setApiKey(config('services.stripe.secret'));

        // Ensure we're in test mode for non-production environments
        if (!app()->environment('production') && !str_starts_with(config('services.stripe.secret'), 'sk_test_')) {
            throw new \Exception('Stripe must use test keys in non-production environments.');
        }
    }

    /**
     * Get test card numbers for different scenarios
     */
    public static function getTestCards(): array
    {
        return [
            'success' => '4242424242424242',
            'authentication_required' => '4000002500003155',
            'decline' => '4000000000000002',
            'insufficient_funds' => '4000000000009995',
            'expired_card' => '4000000000000069'
        ];
    }

    /**
     * Check if we're in test mode
     */
    public function isTestMode(): bool
    {
        return $this->isTestMode;
    }

    /**
     * Create a payout to a user's connected Stripe account
     */
    public function createPayout(User $user, float $amount): array
    {
        try {
            // For test/local environment, return mock data
            if (!app()->environment('production')) {
                Log::info('TEST MODE: Would have sent payout', [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'stripe_account_id' => $user->stripeAccount->account_id ?? 'TEST_ACCOUNT_ID'
                ]);
                
                return [
                    'success' => true,
                    'transaction_id' => 'test_payout_' . uniqid(),
                    'amount' => $amount
                ];
            }

            // For production environment, process real payouts
            // Verify user has a Stripe account connected
            if (!$user->stripeAccount || !$user->stripeAccount->account_id) {
                throw new \Exception('User does not have a connected Stripe account');
            }

            // Create a Transfer to the connected account
            $transfer = Transfer::create([
                'amount' => (int)($amount * 100), // Convert to cents
                'currency' => 'usd',
                'destination' => $user->stripeAccount->account_id,
                'transfer_group' => 'PAYOUT_' . $user->id,
            ]);

            return [
                'success' => true,
                'transaction_id' => $transfer->id,
                'amount' => $amount
            ];

        } catch (\Exception $e) {
            Log::error('Stripe payout failed', [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create or update a user's Stripe Connect account
     */
    public function createConnectedAccount(User $user, array $accountData): array
    {
        try {
            // TODO: Delete after testing - this is test code
            if (app()->environment('local')) {
                Log::info('TEST MODE: Would have created Stripe account', [
                    'user_id' => $user->id,
                    'account_data' => $accountData
                ]);
                
                return [
                    'success' => true,
                    'account_id' => 'acct_test_' . uniqid()
                ];
            }

            // TODO: Uncomment in production
            // Create a Custom Connect account
            // $account = \Stripe\Account::create([
            //     'type' => 'custom',
            //     'country' => 'US',
            //     'email' => $user->email,
            //     'capabilities' => [
            //         'transfers' => ['requested' => true],
            //     ],
            //     'business_type' => 'individual',
            //     'individual' => [
            //         'email' => $user->email,
            //         // Add other required fields from $accountData
            //     ],
            //     'tos_acceptance' => [
            //         'date' => time(),
            //         'ip' => request()->ip()
            //     ],
            // ]);

            // return [
            //     'success' => true,
            //     'account_id' => $account->id
            // ];

        } catch (\Exception $e) {
            Log::error('Stripe account creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function createStripeAccount(User $user): array
    {
        try {
            $account = Account::create([
                'type' => 'express',
                'country' => 'US',
                'email' => $user->email,
                'capabilities' => [
                    'transfers' => ['requested' => true],
                ],
            ]);

            return [
                'account_id' => $account->id,
                'account_enabled' => false,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create Stripe account: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createAccountLink(string $accountId): array
    {
        try {
            $accountLink = AccountLink::create([
                'account' => $accountId,
                'refresh_url' => route('api.v1.stripe.account.link'),
                'return_url' => config('app.frontend_url') . '/settings/payment',
                'type' => 'account_onboarding',
            ]);

            return ['url' => $accountLink->url];
        } catch (\Exception $e) {
            Log::error('Failed to create account link: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createDashboardLink(string $accountId): array
    {
        try {
            $link = Account::createLoginLink($accountId);
            return ['url' => $link->url];
        } catch (\Exception $e) {
            Log::error('Failed to create dashboard link: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process a rental payment with platform fee (80/20 split)
     * 
     * @param Rental $rental
     * @param string $paymentMethodId
     * @return array
     */
    public function processRentalPayment(Rental $rental, string $paymentMethodId): array
    {
        try {
            // Calculate amounts
            $totalAmount = (int)($rental->total_amount * 100); // Convert to cents
            $platformFee = (int)($totalAmount * 0.20); // 20% platform fee
            $ownerAmount = $totalAmount - $platformFee; // 80% to owner
            
            // Get product owner's Stripe account
            $ownerStripeAccount = $rental->offer->product->user->stripeAccount;
            
            if (!$ownerStripeAccount || !$ownerStripeAccount->account_id) {
                throw new \Exception('Product owner does not have a connected Stripe account');
            }
            
            // For local environment, simulate the payment
            if (!app()->environment('production')) {
                Log::info('TEST MODE: Would process rental payment', [
                    'rental_id' => $rental->id,
                    'total_amount' => $totalAmount / 100,
                    'platform_fee' => $platformFee / 100,
                    'owner_amount' => $ownerAmount / 100,
                    'payment_method' => $paymentMethodId,
                    'owner_stripe_account' => $ownerStripeAccount->account_id
                ]);
                
                return [
                    'success' => true,
                    'payment_intent_id' => 'pi_test_' . uniqid(),
                    'platform_fee' => $platformFee / 100,
                    'owner_amount' => $ownerAmount / 100
                ];
            }
            
            // Create payment intent with application fee
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $totalAmount,
                'currency' => 'usd',
                'payment_method' => $paymentMethodId,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'application_fee_amount' => $platformFee,
                'transfer_data' => [
                    'destination' => $ownerStripeAccount->account_id,
                ],
                'metadata' => [
                    'rental_id' => $rental->id,
                    'product_id' => $rental->offer->product_id,
                    'platform_fee' => $platformFee / 100,
                    'owner_amount' => $ownerAmount / 100
                ],
            ]);
            
            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'status' => $paymentIntent->status,
                'platform_fee' => $platformFee / 100,
                'owner_amount' => $ownerAmount / 100
            ];
        } catch (\Exception $e) {
            Log::error('Rental payment processing failed', [
                'rental_id' => $rental->id ?? null,
                'payment_method' => $paymentMethodId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create a payment intent for a rental
     */
    public function createPaymentIntent(Rental $rental): array
    {
        try {
            $amount = $rental->offer->product->price * 100; // Convert to cents
            $description = "Rental payment for {$rental->offer->product->title}";
            
            // Create a PaymentIntent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'usd',
                'description' => $description,
                'metadata' => [
                    'rental_id' => $rental->id,
                    'offer_id' => $rental->offer_id,
                    'product_id' => $rental->offer->product_id,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create payment intent', [
                'rental_id' => $rental->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Refund a payment either partially or fully
     */
    public function refundPayment(string $paymentIntentId, float $amount = null, string $reason = null): array
    {
        try {
            $refundParams = [
                'payment_intent' => $paymentIntentId,
                'reason' => $reason ?: 'requested_by_customer',
            ];
            
            // If amount is provided, it's a partial refund
            if ($amount !== null) {
                $refundParams['amount'] = (int)($amount * 100); // Convert to cents
            }
            
            // In test mode, just simulate the refund
            if ($this->isTestMode && app()->environment('local')) {
                Log::info('TEST MODE: Would have processed refund', [
                    'payment_intent_id' => $paymentIntentId,
                    'amount' => $amount,
                    'reason' => $reason
                ]);
                
                return [
                    'success' => true,
                    'refund_id' => 're_test_' . uniqid(),
                    'amount' => $amount
                ];
            }
            
            // Process the refund with Stripe
            $refund = \Stripe\Refund::create($refundParams);
            
            return [
                'success' => true,
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100, // Convert from cents back to dollars
                'status' => $refund->status
            ];
        } catch (\Exception $e) {
            Log::error('Stripe refund failed', [
                'payment_intent_id' => $paymentIntentId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} 