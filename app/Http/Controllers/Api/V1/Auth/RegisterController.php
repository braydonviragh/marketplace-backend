<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Models\VerificationCode;
use App\Services\SmsService;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\SimpleUserResource;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyPhoneRequest;

class RegisterController extends Controller
{
    use ApiResponse;

    // TODO: Uncomment once SMS is setup
    // protected SmsService $smsService;
    // 
    // public function __construct(SmsService $smsService)
    // {
    //     $this->smsService = $smsService;
    // }

    public function register(RegisterRequest $request)
    {
        // TODO: Uncomment once SMS is setup
        // Generate verification code
        // $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        // 
        // Store verification code
        // VerificationCode::create([
        //     'phone_number' => $request->phone_number,
        //     'code' => $code,
        //     'expires_at' => now()->addMinutes(10),
        // ]);
        //
        // Send SMS
        // $this->smsService->sendVerificationCode($request->phone_number, $code);
        //
        // return $this->successResponse([
        //     'phone_number' => $request->phone_number,
        //     'message' => 'Verification code sent to your phone'
        // ], 'Please verify your phone number', 200);

        // TEMPORARY: Create user directly without verification
        $user = User::create([
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'terms_accepted' => true,
            'terms_accepted_at' => now(),
            'phone_verified_at' => now(), // Auto-verify for testing
            'remember_token' => Str::random(10),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => new SimpleUserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Registration successful. Please complete your profile.');
    }

    // TODO: Uncomment once SMS is setup
    // public function verifyPhone(VerifyPhoneRequest $request)
    // {
    //     $verificationCode = VerificationCode::where('phone_number', $request->phone_number)
    //         ->where('code', $request->code)
    //         ->where('used', false)
    //         ->where('expires_at', '>', now())
    //         ->latest()
    //         ->first();
    //
    //     if (!$verificationCode) {
    //         return $this->errorResponse('Invalid or expired verification code', 422);
    //     }
    //
    //     // Mark code as used
    //     $verificationCode->update(['used' => true]);
    //
    //     // Create user
    //     $user = User::create([
    //         'email' => $request->email,
    //         'phone_number' => $request->phone_number,
    //         'password' => Hash::make($request->password),
    //         'terms_accepted' => true,
    //         'terms_accepted_at' => now(),
    //         'phone_verified_at' => now(),
    //         'remember_token' => Str::random(10),
    //     ]);
    //
    //     $token = $user->createToken('auth_token')->plainTextToken;
    //
    //     return $this->successResponse([
    //         'user' => $user,
    //         'access_token' => $token,
    //         'token_type' => 'Bearer',
    //     ], 'Registration successful');
    // }
} 