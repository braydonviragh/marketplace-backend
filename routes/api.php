<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
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
use App\Http\Controllers\Api\V1\OfferStatusController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\ColorController;
use App\Http\Controllers\Api\V1\LetterSizeController;
use App\Http\Controllers\Api\V1\NumberSizeController;
use App\Http\Controllers\Api\V1\ShoeSizeController;
use App\Http\Controllers\Api\V1\WaistSizeController;
use App\Http\Controllers\Api\V1\Auth\SuperAdminController;
use App\Http\Controllers\Api\V1\Auth\OnboardingController;
use App\Http\Controllers\Api\V1\BalanceController;
use App\Http\Controllers\Api\StyleController;

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
            Route::get('/{id}/products', [ProductController::class, 'userProducts']);
            Route::post('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
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
        Route::prefix('sizes')->group(function () {
            // Letter sizes (XS-XXL)
            Route::get('/letter', [LetterSizeController::class, 'index']);
            Route::post('/letter', [LetterSizeController::class, 'store'])->middleware('role:admin');
            
            // Number sizes (00-22)
            Route::get('/number', [NumberSizeController::class, 'index']);
            Route::post('/number', [NumberSizeController::class, 'store'])->middleware('role:admin');
            
            // Waist sizes (24"-48")
            Route::get('/waist', [WaistSizeController::class, 'index']);
            Route::post('/waist', [WaistSizeController::class, 'store'])->middleware('role:admin');
            
            // Shoe sizes (5-15)
            Route::get('/shoe', [ShoeSizeController::class, 'index']);
            Route::post('/shoe', [ShoeSizeController::class, 'store'])->middleware('role:admin');
        });

        Route::prefix('rentals')->group(function () {
            Route::get('/', [RentalController::class, 'index']);
            Route::get('/{id}', [RentalController::class, 'show']);
            Route::post('/', [RentalController::class, 'store']);
            Route::put('/{rental}', [RentalController::class, 'update']);
            Route::delete('/{rental}', [RentalController::class, 'destroy']);
            Route::post('/{rental}/confirm', [RentalController::class, 'confirm']);
        });

        // Offers
        Route::prefix('offers')->group(function () {
            Route::get('/', [OfferController::class, 'index']);
            Route::get('/sent', [OfferController::class, 'sentOffers']);
            Route::get('/received', [OfferController::class, 'receivedOffers']);
            Route::get('/{offer}', [OfferController::class, 'show']);
            Route::post('/', [OfferController::class, 'store']);
            Route::put('/{offer}', [OfferController::class, 'update']);
            Route::delete('/{offer}', [OfferController::class, 'destroy']);
            Route::post('/{offer}/status', [OfferController::class, 'updateStatus']);
        });
        
        // Products
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::get('/{id}', [ProductController::class, 'show']);
            
            //TODO remove comment when ready
            // Route::middleware(['auth:sanctum'])->group(function () {
                Route::post('/', [ProductController::class, 'store']);
                Route::put('/{product}', [ProductController::class, 'update']);
                    // ->middleware('verify.product.owner');
                Route::delete('/{product}', [ProductController::class, 'destroy']);
                    // ->middleware('verify.product.owner');
            // });
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

        Route::get('/balance', [BalanceController::class, 'getBalance']);
        Route::post('/balance/withdraw', [BalanceController::class, 'withdraw']);
        Route::get('/transactions', [BalanceController::class, 'getTransactions']);
        Route::get('/offer-statuses', [OfferStatusController::class, 'index']);
        Route::get('/styles', [StyleController::class, 'index']);
    // });
}); 