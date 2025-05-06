<?php

use Illuminate\Support\Facades\Route;
// First, the raw health check route - NO MIDDLEWARE!
Route::get('/health', function () {
    return response('OK', 200)
        ->header('Content-Type', 'text/plain');
})->withoutMiddleware(['web', 'api', \Illuminate\Http\Middleware\HandleCors::class]); // Remove all middleware to ensure it always responds

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
use App\Http\Controllers\Api\V1\StripeController;
use App\Http\Controllers\Api\V1\Auth\PhoneVerificationController;
use App\Http\Controllers\Api\V1\CountryController;
use App\Http\Controllers\Api\V1\ProvinceController;
use App\Http\Controllers\Api\V1\UserProfileController;
use App\Http\Controllers\Api\V1\StripeWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Removed duplicate health check route

    // Test route for product endpoint
    Route::get('/product-test', [ProductController::class, 'test']);

    // Public Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [LoginController::class, 'login']);
        Route::post('register', [RegisterController::class, 'register']);
        
        // Check email/phone availability
        Route::post('check-availability', [RegisterController::class, 'checkAvailability']);
        
        // Phone Verification
        Route::post('phone/send', [PhoneVerificationController::class, 'sendCode']);
        Route::post('phone/verify', [PhoneVerificationController::class, 'verifyCode']);
        Route::post('phone/resend', [PhoneVerificationController::class, 'resendCode']);
        
        // Email Verification
        Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
            ->name('verification.verify');
        
        // Password Reset
        Route::post('forgot-password', [PasswordResetController::class, 'initiateReset'])
            ->middleware('throttle:6,1')
            ->name('password.request');
        Route::post('reset-password', [PasswordResetController::class, 'resetWithCode'])
            ->middleware('throttle:6,1')
            ->name('password.update');
            
        // Protected auth routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [LoginController::class, 'logout']);
            Route::post('refresh', [LoginController::class, 'refresh']);
            Route::post('email/resend', [VerificationController::class, 'resend'])
                ->middleware('throttle:6,1')
                ->name('verification.resend');
        });
    });

    // Countries and Provinces - Public Routes
    Route::get('/countries', [CountryController::class, 'index']);
    Route::get('/provinces', [ProvinceController::class, 'index']);
    Route::get('/offer-statuses', [OfferStatusController::class, 'index']);
    Route::get('/styles', [StyleController::class, 'index']);
    
    // Categories - Public routes
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{category}', [CategoryController::class, 'show']);
    });
    
    // Brands - Public routes
    Route::get('brands', [BrandController::class, 'index']);
    Route::get('brands/{brand}', [BrandController::class, 'show']);
    
    // Colors - Public routes
    Route::get('colors', [ColorController::class, 'index']);
    Route::get('colors/{color}', [ColorController::class, 'show']);
    
    // Sizes - Public routes
    Route::prefix('sizes')->group(function () {
        // Letter sizes (XS-XXL)
        Route::get('/letter', [LetterSizeController::class, 'index']);
        
        // Number sizes (00-22)
        Route::get('/number', [NumberSizeController::class, 'index']);
        
        // Waist sizes (24"-48")
        Route::get('/waist', [WaistSizeController::class, 'index']);
        
        // Shoe sizes (5-15)
        Route::get('/shoe', [ShoeSizeController::class, 'index']);
    });
    
    // Product public routes
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{id}', [ProductController::class, 'show']);
    });
    // User Profile Route
    Route::post('/user/profile', [UserProfileController::class, 'store']);

    // Users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/current-user', [UserController::class, 'currentUser']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}/products', [ProductController::class, 'userProducts']);
        Route::post('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
        Route::get('/{id}/rentals', [RentalController::class, 'userRentals']);
        Route::get('/{id}/payments', [PaymentController::class, 'userPayments'])
            ->middleware('verify.user.access');
    });
    // Protected routes that require authentication
    Route::middleware(['auth:sanctum'])->group(function () {

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
            Route::post('/confirm', [PaymentController::class, 'confirm']);
        });
        
        // Balance & Transactions
        Route::get('/balance', [BalanceController::class, 'getBalance']);
        Route::post('/balance/withdraw', [BalanceController::class, 'withdraw']);
        Route::get('/transactions', [BalanceController::class, 'getTransactions']);

        // Stripe Account Routes
        Route::prefix('stripe')->group(function () {
            Route::get('/account', [StripeController::class, 'getAccount']);
            Route::post('/account', [StripeController::class, 'createAccount']);
            Route::get('/account/link', [StripeController::class, 'getAccountLink']);
            Route::get('/dashboard', [StripeController::class, 'getDashboardLink']);
        });

        // Admin routes for viewing any user's balance and transactions
        Route::middleware(['role:admin'])->group(function () {
            Route::get('/admin/users/{userId}/balance', [BalanceController::class, 'getUserBalance']);
            Route::get('/admin/users/{userId}/transactions', [BalanceController::class, 'getUserTransactions']);
        });
        
        // Super admin routes
        Route::middleware(['role:super_admin'])->group(function () {
            Route::prefix('admin/super-admins')->group(function () {
                Route::get('/', [SuperAdminController::class, 'index']);
                Route::post('/', [SuperAdminController::class, 'store']);
                Route::put('/{superAdmin}', [SuperAdminController::class, 'update']);
                Route::post('/{superAdmin}/deactivate', [SuperAdminController::class, 'deactivate']);
                Route::post('/{superAdmin}/reactivate', [SuperAdminController::class, 'reactivate']);
            });
        });

        // Categories - Admin protected routes
        Route::prefix('categories')->middleware(['role:admin'])->group(function () {
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('/{category}', [CategoryController::class, 'update']);
            Route::delete('/{category}', [CategoryController::class, 'destroy']);
            Route::patch('/{category}/toggle-status', [CategoryController::class, 'toggleStatus']);
        });
        
        // Brands - Admin protected routes
        Route::prefix('brands')->middleware(['role:admin'])->group(function () {
            Route::post('/', [BrandController::class, 'store']);
            Route::put('/{brand}', [BrandController::class, 'update']);
            Route::delete('/{brand}', [BrandController::class, 'destroy']);
        });
        
        // Colors - Admin protected routes
        Route::prefix('colors')->middleware(['role:admin'])->group(function () {
            Route::post('/', [ColorController::class, 'store']);
            Route::put('/{color}', [ColorController::class, 'update']);
            Route::delete('/{color}', [ColorController::class, 'destroy']);
        });
        
        // Sizes - Admin protected routes
        Route::prefix('sizes')->middleware(['role:admin'])->group(function () {
            Route::post('/letter', [LetterSizeController::class, 'store']);
            Route::post('/number', [NumberSizeController::class, 'store']);
            Route::post('/waist', [WaistSizeController::class, 'store']);
            Route::post('/shoe', [ShoeSizeController::class, 'store']);
        });
        
        // Rentals
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
        
        // Products - Protected routes
        Route::prefix('products')->group(function () {
            Route::post('/', [ProductController::class, 'store']);
            Route::put('/{product}', [ProductController::class, 'update']);
            Route::delete('/{product}', [ProductController::class, 'destroy']);
        });
    });
        Route::get('/offer-statuses', [OfferStatusController::class, 'index']);
        Route::get('/styles', [StyleController::class, 'index']);

    // Stripe Webhook Route - no CSRF or auth middleware
    Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('stripe.webhook');

