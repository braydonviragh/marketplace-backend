<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StripeAccountResource;
use App\Repositories\StripeRepository;
use App\Services\StripeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class StripeController extends Controller
{
    use ApiResponse;
    
    public function __construct(
        protected StripeRepository $stripeRepository,
        protected StripeService $stripeService
    ) {}

    public function getAccount(): JsonResponse
    {
        $user = Auth::user();
        $account = $this->stripeRepository->getAccount($user);

        return $this->successResponse(
            $account ? new StripeAccountResource($account) : null,
            'Stripe account retrieved successfully'
        );
    }

    public function createAccount(): JsonResponse
    {
        $user = Auth::user();

        if ($user->stripeAccount) {
            return $this->errorResponse('User already has a Stripe account', 400);
        }

        try {
            $accountData = $this->stripeService->createStripeAccount($user);
            $account = $this->stripeRepository->createAccount($user, $accountData);

            return $this->successResponse(
                new StripeAccountResource($account),
                'Stripe account created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create Stripe account: ' . $e->getMessage(), 500);
        }
    }

    public function getAccountLink(): JsonResponse
    {
        $user = Auth::user();
        $account = $this->stripeRepository->getAccount($user);

        if (!$account) {
            return $this->errorResponse('No Stripe account found', 404);
        }

        try {
            $link = $this->stripeService->createAccountLink($account->account_id);
            return $this->successResponse($link, 'Account link created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create account link: ' . $e->getMessage(), 500);
        }
    }

    public function getDashboardLink(): JsonResponse
    {
        $user = Auth::user();
        $account = $this->stripeRepository->getAccount($user);

        if (!$account) {
            return $this->errorResponse('No Stripe account found', 404);
        }

        try {
            $link = $this->stripeService->createDashboardLink($account->account_id);
            return $this->successResponse($link, 'Dashboard link created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create dashboard link: ' . $e->getMessage(), 500);
        }
    }
} 