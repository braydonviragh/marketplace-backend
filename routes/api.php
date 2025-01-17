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
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\ColorController;
use App\Http\Controllers\Api\V1\SizeController;
use App\Http\Controllers\Api\V1\NumberSizeController;
use App\Http\Controllers\Api\V1\ShoeSizeController;
use App\Http\Controllers\Api\V1\WaistSizeController;
use App\Http\Controllers\Api\V1\Auth\SuperAdminController;
use App\Http\Controllers\Api\V1\Auth\OnboardingController;

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
        
        // Registration
        Route::post('register', [RegisterController::class, 'register']);
        
        // Email Verification
        Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
            ->name('verification.verify');
        Route::post('email/resend', [VerificationController::class, 'resend'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('verification.resend');
        
        // Password Reset
        Route::post('forgot-password', [PasswordResetController::class, 'initiateReset'])
            ->middleware('throttle:6,1')
            ->name('password.request');
        Route::post('reset-password', [PasswordResetController::class, 'resetWithCode'])
            ->middleware('throttle:6,1')
            ->name('password.update');
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

        // Onboarding
        Route::post('onboarding/complete', [OnboardingController::class, 'complete']);

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

         // Brands
        Route::apiResource('brands', BrandController::class);
            
        // Colors
        Route::apiResource('colors', ColorController::class);
        
        // Sizes
        Route::apiResource('letter-sizes', LetterSizeController::class);
        Route::apiResource('number-sizes', NumberSizeController::class);
        Route::apiResource('shoe-sizes', ShoeSizeController::class);
        Route::apiResource('waist-sizes', WaistSizeController::class);

        Route::prefix('rentals')->group(function () {
            Route::get('/', [RentalController::class, 'index']);
            Route::get('/{id}', [RentalController::class, 'show']);
            Route::post('/', [RentalController::class, 'store']);
            Route::put('/{rental}', [RentalController::class, 'update']);
            Route::delete('/{rental}', [RentalController::class, 'destroy']);
            Route::post('/{rental}/confirm', [RentalController::class, 'confirm']);
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


        // Super Admin Management
        Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
            Route::prefix('admin/super-admins')->group(function () {
                Route::get('/', [SuperAdminController::class, 'index']);
                Route::post('/', [SuperAdminController::class, 'store']);
                Route::put('/{superAdmin}', [SuperAdminController::class, 'update']);
                Route::post('/{superAdmin}/deactivate', [SuperAdminController::class, 'deactivate']);
                Route::post('/{superAdmin}/reactivate', [SuperAdminController::class, 'reactivate']);
            });
        });
    // });
}); 