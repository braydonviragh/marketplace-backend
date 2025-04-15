<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Models\VerificationCode;
use App\Services\PhoneVerificationService;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\SimpleUserResource;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyPhoneRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

class RegisterController extends Controller
{
    use ApiResponse;

    protected $verificationService;
    
    public function __construct(PhoneVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    public function register(RegisterRequest $request)
    {
        // Get the phone number from the request
        $phoneNumber = $request->phone_number;
        
        // Check if we're in local environment - automatically verify phone
        $isLocalEnv = env('APP_ENV') === 'local';
        
        // Check if the phone number is verified in the session
        $verifiedPhones = session('verified_phones', []);
        $isVerified = in_array($phoneNumber, $verifiedPhones);
        
        // In local environment, always consider the phone verified for testing
        if ($isLocalEnv && !$isVerified) {
            $isVerified = true;
            \Illuminate\Support\Facades\Log::info("Local environment: Auto-verifying phone number {$phoneNumber} for registration");
        }
        
        // If not verified, return an error
        if (!$isVerified) {
            return $this->errorResponse(
                'Phone number must be verified before registration.',
                422
            );
        }

        // Create user with verified phone
        $user = User::create([
            'email' => $request->email,
            'phone_number' => $phoneNumber,
            'password' => Hash::make($request->password),
            'terms_accepted' => true,
            'terms_accepted_at' => now(),
            'remember_token' => Str::random(10),
            'phone_verified_at' => now(), // Set phone_verified_at since it's already verified
        ]);
        
        // Create user profile if profile data is provided
        if ($request->has('username')) {
            // Create profile
            $user->profile()->create([
                'username' => $request->username,
                'name' => $request->name,
                'city' => $request->city,
                'postal_code' => $request->postal_code,
                'province_id' => $request->province_id,
                'country_id' => $request->country_id,
            ]);
            
            // Handle profile picture if provided
            if ($request->hasFile('profile_picture')) {
                $profilePicturePath = $request->file('profile_picture')->store('profiles', 'public');
                $user->profile()->update(['profile_picture' => $profilePicturePath]);
            }
            
            // Load the profile relationship
            $user->load('profile');
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;
        
        // Remove the phone number from the verified phones session
        $verifiedPhones = array_diff($verifiedPhones, [$phoneNumber]);
        session(['verified_phones' => $verifiedPhones]);

        return $this->successResponse([
            'user' => new SimpleUserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'User registered successfully', 201);
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

    public function checkAvailability(Request $request)
    {

        // Define the validation rules
        $rules = [
            'email' => 'sometimes|required|email',
            'phone_number' => 'sometimes|required|string|regex:/^\+1[0-9]{10}$/',
        ];

        // Create a validator instance
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $errors = [];
        
        // Check email if provided
        if ($request->has('email')) {
            $emailExists = User::where('email', $request->email)->exists();
            if ($emailExists) {
                $errors['email'] = 'This email address is already in use.';
            }
        }
        
        // Check phone number if provided
        if ($request->has('phone_number')) {
            $phoneExists = User::where('phone_number', $request->phone_number)->exists();
            if ($phoneExists) {
                $errors['phone_number'] = 'This phone number is already in use.';
            }
        }
        
        // Return error response if there are any validation errors
        if (!empty($errors)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $errors
            ], 422);
        }
        
        // If we get here, both email and phone are available
        return response()->json([
            'status' => 'success',
            'message' => 'Email and phone number are available',
            'data' => [
                'email_available' => $request->has('email'),
                'phone_available' => $request->has('phone_number')
            ]
        ]);
    }
} 