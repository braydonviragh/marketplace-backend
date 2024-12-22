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
    ];

    protected $middlewareGroups = [
        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];
} 