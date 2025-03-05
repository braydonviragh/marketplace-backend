<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PhoneVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class PhoneVerificationController extends Controller
{
    protected $verificationService;

    public function __construct(PhoneVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Send a verification code to the provided phone number
     */
    public function sendCode(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/',
        ]);

        $phoneNumber = $request->input('phone_number');
        
        try {
            // Check if this is for an existing user during registration
            $user = User::where('phone_number', $phoneNumber)->first();
            
            if ($user && $user->phone_verified_at) {
                return response()->json([
                    'message' => 'Phone number already verified',
                    'verified' => true
                ], 200);
            }
            
            // Store phone number in session for later verification
            session(['verification_phone_number' => $phoneNumber]);
            
            $this->verificationService->sendVerificationCode($phoneNumber);
            
            return response()->json([
                'message' => 'Verification code sent successfully',
                'phone_number' => $phoneNumber
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to send verification code: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to send verification code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify the code provided by the user
     */
    public function verifyCode(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'code' => 'required|string|size:6',
            'phone_number' => 'sometimes|string|regex:/^\+?[1-9]\d{1,14}$/',
        ]);
        
        // Get the code from the request
        $code = $request->input('code');
        
        // Try to get phone number from various sources
        $phoneNumber = null;
        
        // 1. Check if phone_number is in the request
        if ($request->has('phone_number')) {
            $phoneNumber = $request->input('phone_number');
        } 
        // 2. Check if user is authenticated
        elseif (Auth::check()) {
            $phoneNumber = Auth::user()->phone_number;
        }
        // 3. Check if phone_number is in the session
        elseif (session()->has('verification_phone_number')) {
            $phoneNumber = session('verification_phone_number');
        }
        
        // If no phone number found, return error
        if (!$phoneNumber) {
            return response()->json([
                'message' => 'Phone number not found. Please provide a phone number.',
                'verified' => false
            ], 400);
        }
        
        try {
            $isValid = $this->verificationService->verifyCode($phoneNumber, $code);
            
            if (!$isValid) {
                return response()->json([
                    'message' => 'Invalid verification code',
                    'verified' => false
                ], 400);
            }
            
            // Check if this is for an existing user
            $user = User::where('phone_number', $phoneNumber)->first();
            
            if ($user) {
                // Mark the user's phone as verified
                $user->phone_verified_at = now();
                $user->save();
                
                // If the user is not authenticated, generate a token
                if (!Auth::check()) {
                    $token = $user->createToken('auth_token')->plainTextToken;
                    
                    return response()->json([
                        'message' => 'Phone number verified successfully',
                        'verified' => true,
                        'user' => $user,
                        'token' => $token
                    ], 200);
                }
                
                return response()->json([
                    'message' => 'Phone number verified successfully',
                    'verified' => true,
                    'user' => $user
                ], 200);
            }
            
            // If no user exists with this phone number, store it in session as verified
            // This will be used during registration to confirm the phone is verified
            $verifiedPhones = session('verified_phones', []);
            $verifiedPhones[] = $phoneNumber;
            session(['verified_phones' => $verifiedPhones]);
            
            return response()->json([
                'message' => 'Phone number verified successfully',
                'verified' => true,
                'phone_number' => $phoneNumber
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to verify code: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to verify code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resend a verification code to the authenticated user
     */
    public function resendCode(Request $request): JsonResponse
    {
        if (!Auth::check() && !$request->has('phone_number')) {
            return response()->json([
                'message' => 'Please provide a phone number or log in'
            ], 400);
        }
        
        $phoneNumber = null;
        
        if (Auth::check()) {
            $user = Auth::user();
            $phoneNumber = $user->phone_number;
            
            if (!$phoneNumber) {
                return response()->json([
                    'message' => 'User does not have a phone number'
                ], 400);
            }
        } else {
            $phoneNumber = $request->input('phone_number');
        }
        
        try {
            $this->verificationService->sendVerificationCode($phoneNumber);
            
            return response()->json([
                'message' => 'Verification code resent successfully',
                'phone_number' => $phoneNumber
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to resend verification code: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to resend verification code',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 