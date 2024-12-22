<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;

class VerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return $this->errorResponse('Invalid verification link', 'INVALID_VERIFICATION', 400);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->errorResponse('Email already verified', 'ALREADY_VERIFIED', 400);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->successResponse(null, 'Email verified successfully');
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->errorResponse('Email already verified', 'ALREADY_VERIFIED', 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->successResponse(null, 'Verification link resent');
    }
} 