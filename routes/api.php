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
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        // Users
        Route::prefix('users')->middleware(['throttle:api.users'])->group(function () {
            Route::get('/', [UserController::class, 'index'])->middleware('permission:view-users');
            Route::get('/{id}', [UserController::class, 'show'])->middleware('permission:view-users');
            Route::get('/{id}/reviews', [ReviewController::class, 'userReviews']);
            Route::get('/{id}/rentals', [RentalController::class, 'userRentals']);
            Route::get('/{id}/payments', [PaymentController::class, 'userPayments'])
                ->middleware('verify.user.access');
        });

        // Reviews with rate limiting and permissions
        Route::prefix('reviews')->middleware(['throttle:api.reviews'])->group(function () {
            Route::get('/', [ReviewController::class, 'index']);
            Route::get('/{id}', [ReviewController::class, 'show']);
            Route::post('/', [ReviewController::class, 'store'])
                ->middleware(['verified', 'complete.rental']);
            Route::put('/{id}', [ReviewController::class, 'update'])
                ->middleware('verify.review.owner');
            Route::delete('/{id}', [ReviewController::class, 'destroy'])
                ->middleware('permission:delete-reviews');
        });

        // Rentals with specific middleware
        Route::prefix('rentals')->middleware(['throttle:api.rentals'])->group(function () {
            Route::get('/', [RentalController::class, 'index']);
            Route::get('/{id}', [RentalController::class, 'show']);
            Route::post('/', [RentalController::class, 'store'])
                ->middleware(['verified', 'check.user.status']);
            Route::put('/{id}', [RentalController::class, 'update'])
                ->middleware('verify.rental.owner');
            Route::patch('/{id}/status', [RentalController::class, 'updateStatus'])
                ->middleware(['verify.rental.participant']);
        });

        // Payments with additional security
        Route::prefix('payments')->middleware([
            'throttle:api.payments',
            'verify.payment.access'
        ])->group(function () {
            Route::get('/', [PaymentController::class, 'index']);
            Route::get('/{id}', [PaymentController::class, 'show']);
            Route::post('/', [PaymentController::class, 'store'])
                ->middleware('verify.payment.amount');
            Route::post('/{id}/refund', [PaymentController::class, 'refund'])
                ->middleware('permission:process-refunds');
        });

        // Notifications
        Route::prefix('notifications')->middleware(['throttle:api.notifications'])->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])
                ->middleware('verify.notification.owner');
            Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        });
    });
}); 