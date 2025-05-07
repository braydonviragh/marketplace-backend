<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middlewareAliases = [
        // ... existing middleware ...
        'verify.user.access' => \App\Http\Middleware\VerifyUserAccess::class,
        'verify.rental.participant' => \App\Http\Middleware\VerifyRentalParticipant::class,
        'verify.review.owner' => \App\Http\Middleware\VerifyReviewOwner::class,
        'verify.payment.access' => \App\Http\Middleware\VerifyPaymentAccess::class,
        'verify.notification.owner' => \App\Http\Middleware\VerifyNotificationOwner::class,
        'complete.rental' => \App\Http\Middleware\EnsureRentalComplete::class,
        'check.user.status' => \App\Http\Middleware\CheckUserStatus::class,
        'verify.product.owner' => \App\Http\Middleware\VerifyProductOwner::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\EnsureCorsHeaders::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \App\Http\Middleware\IncreaseUploadLimits::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];
    
    /**
     * The middleware that should be excluded from the specified URIs.
     *
     * @var array<int, string>
     */
    protected $middlewareExceptPaths = [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class => [
            'api/health',
            'api/*/health',
            'health',
            'healthz.php',
            'v1/health'
        ],
        \Illuminate\Http\Middleware\HandleCors::class => [
            'healthz.php'
        ]
    ];
} 