<?php

namespace App\Jobs;

use App\Models\Rental;
use App\Models\Payment;
use App\Models\UserBalance;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPendingBalances implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting to process pending balances');
        
        // Find completed rentals with pending balances
        $completedRentals = Rental::with(['payment'])
            ->whereHas('payment', function ($query) {
                $query->where('status', 'completed');
            })
            ->where('end_date', '<', Carbon::now())
            ->where('is_balance_released', false)
            ->get();
            
        Log::info('Found completed rentals: ' . $completedRentals->count());
        
        foreach ($completedRentals as $rental) {
            try {
                DB::beginTransaction();
                
                $payment = $rental->payment;
                $payeeId = $payment->payee_id;
                
                // Get user balance
                $userBalance = UserBalance::where('user_id', $payeeId)->first();
                
                if (!$userBalance) {
                    Log::warning('User balance not found, skipping rental: ' . $rental->id);
                    continue;
                }
                
                // Amount to release from pending to available balance
                $amount = $payment->owner_amount;
                
                // Check if user has enough in pending balance
                if ($userBalance->pending_balance >= $amount) {
                    // Move from pending to available
                    $userBalance->pending_balance -= $amount;
                    $userBalance->available_balance += $amount;
                    $userBalance->save();
                    
                    // Create transaction record for the balance movement
                    Transaction::create([
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                        'user_id' => $payeeId,
                        'type' => 'internal',
                        'status' => 'completed',
                        'amount' => $amount,
                        'fee' => 0,
                        'description' => 'Rental payment released to available balance',
                        'transaction_type' => 'balance_release',
                        'source_id' => $rental->id,
                        'source_type' => 'App\\Models\\Rental',
                    ]);
                    
                    // Update rental to mark balance as released
                    $rental->is_balance_released = true;
                    $rental->balance_released_at = Carbon::now();
                    $rental->save();
                    
                    Log::info('Released pending balance for rental: ' . $rental->id . ', amount: ' . $amount);
                } else {
                    Log::warning('Insufficient pending balance for rental: ' . $rental->id);
                }
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error processing pending balance for rental: ' . $rental->id, [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        Log::info('Completed processing pending balances');
    }
} 