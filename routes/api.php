<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\RentalController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\SocialAuthController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\VerificationController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ListingController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\OfferController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Public Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [LoginController::class, 'login']);
        Route::post('logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
        Route::post('refresh', [LoginController::class, 'refresh'])->middleware('auth:sanctum');
        
        // Social Authentication
        Route::prefix('social')->group(function () {
            Route::get('{provider}/redirect', [SocialAuthController::class, 'redirect']);
            Route::get('{provider}/callback', [SocialAuthController::class, 'callback']);
        });

        // Registration
        Route::post('register', [RegisterController::class, 'register']);
        
        // Email Verification
        Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
            ->name('verification.verify');
        Route::post('email/resend', [VerificationController::class, 'resend'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('verification.resend');
        
        // Password Reset
        Route::post('forgot-password', [PasswordResetController::class, 'forgotPassword'])
            ->middleware('throttle:6,1')
            ->name('password.email');
        Route::post('reset-password', [PasswordResetController::class, 'reset'])
            ->middleware('throttle:6,1')
            ->name('password.reset');
    });

    // Protected routes with specific middleware
    // Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    
        // Users
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::get('/{id}/reviews', [ReviewController::class, 'userReviews']);
            Route::get('/{id}/rentals', [RentalController::class, 'userRentals']);
            Route::get('/{id}/payments', [PaymentController::class, 'userPayments'])
                ->middleware('verify.user.access');
        });

        // Rentals
        Route::prefix('rentals')->group(function () {
            Route::get('/', [RentalController::class, 'index']);
            Route::get('/{id}', [RentalController::class, 'show']);
            Route::post('/', [RentalController::class, 'store'])
                ->middleware(['verified', 'check.user.status']);
            Route::put('/{id}', [RentalController::class, 'update'])
                ->middleware('verify.rental.owner');
            Route::patch('/{id}/status', [RentalController::class, 'updateStatus'])
                ->middleware(['verify.rental.participant']);
        });

        // Payments
        Route::prefix('payments')->group(function () {
            Route::get('/', [PaymentController::class, 'index']);
            Route::get('/{id}', [PaymentController::class, 'show']);
            Route::post('/', [PaymentController::class, 'store'])
                ->middleware('verify.payment.amount');
            Route::post('/{id}/refund', [PaymentController::class, 'refund'])
                ->middleware('permission:process-refunds');
        });

        // Categories
        Route::prefix('categories')->group(function () {
            // Public routes
            Route::get('/', [CategoryController::class, 'index']);
            Route::get('/{category}', [CategoryController::class, 'show']);

            // Protected routes
            Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
                Route::post('/', [CategoryController::class, 'store']);
                Route::put('/{category}', [CategoryController::class, 'update']);
                Route::delete('/{category}', [CategoryController::class, 'destroy']);
                Route::patch('/{category}/toggle-status', [CategoryController::class, 'toggleStatus']);
            });
        });

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::prefix('rentals')->group(function () {
                Route::get('/', [RentalController::class, 'index']);
                Route::get('/{id}', [RentalController::class, 'show']);
                Route::post('/', [RentalController::class, 'store']);
                Route::put('/{rental}', [RentalController::class, 'update']);
                Route::delete('/{rental}', [RentalController::class, 'destroy']);
            });
        });
        
        // Products
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::get('/{id}', [ProductController::class, 'show']);
            Route::post('/', [ProductController::class, 'store'])
                ->middleware(['verified', 'check.user.status']);
            Route::put('/{product}', [ProductController::class, 'update'])
                ->middleware('verify.product.owner');
        });

        // Offers
        Route::prefix('offers')->group(function () {
            Route::get('/', [OfferController::class, 'index']);
            Route::post('/', [OfferController::class, 'store']);
            Route::put('/{offer}', [OfferController::class, 'update'])
                ->middleware('verify.offer.owner');
            Route::post('/{offer}/accept', [OfferController::class, 'accept'])
                ->middleware('verify.product.owner');
            Route::post('/{offer}/reject', [OfferController::class, 'reject'])
                ->middleware('verify.product.owner');
        });
    // });
}); 