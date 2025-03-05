<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StripeAccountResource;
use App\Repositories\StripeRepository;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class StripeController extends Controller
{
    public function __construct(
        protected StripeRepository $stripeRepository,
        protected StripeService $stripeService
    ) {}

    public function getAccount(): JsonResponse
    {
     //$user = Auth::user();
     $user = User::find(1);
     $account = $this->stripeRepository->getAccount($user);

        return response()->json([
            'success' => true,
            'data' => $account ? new StripeAccountResource($account) : null
        ]);
    }

    public function createAccount(): JsonResponse
    {
        //$user = Auth::user();
        $user = User::find(1);

        if ($user->stripeAccount) {
            return response()->json([
                'success' => false,
                'message' => 'User already has a Stripe account'
            ], 400);
        }

        try {
            $accountData = $this->stripeService->createStripeAccount($user);
            $account = $this->stripeRepository->createAccount($user, $accountData);

            return response()->json([
                'success' => true,
                'data' => new StripeAccountResource($account)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Stripe account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAccountLink(): JsonResponse
    {
     //$user = Auth::user();
     $user = User::find(1);
     $account = $this->stripeRepository->getAccount($user);

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'No Stripe account found'
            ], 404);
        }

        try {
            $link = $this->stripeService->createAccountLink($account->account_id);
            return response()->json([
                'success' => true,
                'data' => $link
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create account link: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDashboardLink(): JsonResponse
    {
        //$user = Auth::user();
        $user = User::find(1);
        $account = $this->stripeRepository->getAccount($user);

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'No Stripe account found'
            ], 404);
        }

        try {
            $link = $this->stripeService->createDashboardLink($account->account_id);
            return response()->json([
                'success' => true,
                'data' => $link
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create dashboard link: ' . $e->getMessage()
            ], 500);
        }
    }
} 