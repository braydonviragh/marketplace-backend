<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Models\VerificationCode;
use App\Services\SmsService;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\ForgotPasswordInitRequest;
use App\Http\Requests\Auth\ResetPasswordWithCodeRequest;
use App\Traits\ApiResponse;

class PasswordResetController extends Controller
{
    use ApiResponse;

    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function initiateReset(ForgotPasswordInitRequest $request)
    {
        $user = User::where('email', $request->identifier)
            ->orWhere('phone_number', $request->identifier)
            ->first();

        if (!$user) {
            return $this->errorResponse('No user found with this identifier', 404);
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store verification code
        $verificationCode = VerificationCode::create([
            'email' => $request->method === 'email' ? $user->email : null,
            'phone_number' => $request->method === 'sms' ? $user->phone_number : null,
            'code' => $code,
            'type' => 'password_reset',
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send code via chosen method
        if ($request->method === 'sms') {
            $this->smsService->sendVerificationCode($user->phone_number, $code);
            $message = 'Reset code sent to your phone number';
        } else {
            // Send email with code
            // TODO: Implement email sending
            $message = 'Reset code sent to your email';
        }

        return $this->successResponse(null, $message);
    }

    public function resetWithCode(ResetPasswordWithCodeRequest $request)
    {
        $user = User::where('email', $request->identifier)
            ->orWhere('phone_number', $request->identifier)
            ->first();

        if (!$user) {
            return $this->errorResponse('No user found with this identifier', 404);
        }

        $verificationCode = VerificationCode::where(function ($query) use ($request, $user) {
                $query->where('email', $user->email)
                    ->orWhere('phone_number', $user->phone_number);
            })
            ->where('code', $request->code)
            ->where('type', 'password_reset')
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$verificationCode) {
            return $this->errorResponse('Invalid or expired code', 422);
        }

        // Mark code as used
        $verificationCode->update(['used' => true]);

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ]);

        return $this->successResponse(null, 'Password has been reset successfully');
    }
} 