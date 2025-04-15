<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Models\UserProfile;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class RegisterController extends Controller
{
    use ApiResponse;

    protected $verificationService;
    
    public function __construct(PhoneVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Log the registration attempt with key data for debugging
            Log::info('Registration attempt', [
                'email' => $data['email'],
                'phone_number' => $data['phone_number'],
                'has_style_id' => isset($data['style_id']),
                'username' => $data['username'] ?? null,
                'city' => $data['city'] ?? null
            ]);

            $activationCode = $this->getActivationCode();

            // Create user
            $user = User::create([
                'email' => $data['email'],
                'phone_number' => $data['phone_number'],
                'password' => Hash::make($data['password']),
                'phone_verification_code' => $activationCode,
                'phone_verification_code_expire_at' => now()->addMinutes(10),
                'phone_verified_at' => null
            ]);

            Log::info('User created', ['user_id' => $user->id]);

            // Create user profile
            try {
                $userProfileData = [
                    'user_id' => $user->id,
                    'username' => $data['username'] ?? null,
                    'name' => $data['name'] ?? null,
                    'city' => $data['city'] ?? null,
                    'postal_code' => $data['postal_code'] ?? null,
                    'country_id' => $data['country_id'] ?? null,
                    'province_id' => $data['province_id'] ?? null,
                    'style_id' => $data['style_id'] ?? null
                ];

                // Only include non-null values
                $userProfileData = array_filter($userProfileData, function ($value) {
                    return $value !== null;
                });

                Log::info('Creating user profile', $userProfileData);
                
                $userProfile = UserProfile::create($userProfileData);

                Log::info('User profile created', ['profile_id' => $userProfile->id]);

                // Check if user profile was created successfully
                if (!$userProfile) {
                    throw new \Exception('Failed to create user profile');
                }
            } catch (\Exception $e) {
                Log::error('User profile creation failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }

            // Send verification code
            try {
                if (app()->environment('production')) {
                    $this->sendOtp($user->phone_number, $activationCode);
                }
            } catch (\Exception $e) {
                Log::error('SMS sending error', [
                    'phone' => $user->phone_number,
                    'code' => $activationCode,
                    'error' => $e->getMessage()
                ]);
                // Continue even if SMS fails - user can request a new code
            }

            DB::commit();

            // Create a response based on the environment
            $responseData = ['user_id' => $user->id];
            
            // In local environment, we can auto-generate a token for easier testing
            if (app()->environment('local')) {
                $token = $user->createToken('auth_token')->plainTextToken;
                $responseData['access_token'] = $token;
                $responseData['token_type'] = 'Bearer';
            }

            return $this->success(
                $responseData, 
                'Registration successful! Please verify your phone number.'
            );
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('Registration query exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            $errorMessage = $this->formatDatabaseError($e);
            return $this->error($errorMessage, 'DATABASE_ERROR', 422);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Registration error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->error(
                'An error occurred during registration: ' . $e->getMessage(), 
                'REGISTRATION_ERROR', 
                500
            );
        }
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

    /**
     * Format database error message for user-friendly display
     *
     * @param \Exception $exception
     * @return string
     */
    private function formatDatabaseError(\Exception $exception): string
    {
        // Log the detailed error for debugging
        Log::error('Registration database error', [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // For MySQL integrity constraint violations (code 23000)
        if ($exception->getCode() == 23000) {
            $message = $exception->getMessage();
            
            // Extract field details from error message
            if (str_contains($message, 'Duplicate entry')) {
                if (str_contains($message, 'users.email')) {
                    return 'This email address is already in use.';
                } elseif (str_contains($message, 'users.phone_number')) {
                    return 'This phone number is already registered.';
                } elseif (str_contains($message, 'user_profiles.username')) {
                    return 'This username is already taken.';
                }
                return 'One of the provided fields is already in use.';
            }
            
            // Foreign key constraint failures
            if (str_contains($message, 'style_id')) {
                return 'Please select a valid style (Man, Woman, or Unisex).';
            } elseif (str_contains($message, 'province_id')) {
                return 'The selected province is invalid.';
            } elseif (str_contains($message, 'country_id')) {
                return 'The selected country is invalid.';
            }
        }

        // Check for common missing required fields
        if (str_contains($exception->getMessage(), 'style_id')) {
            return 'Style preference is required. Please select Man, Woman, or Unisex.';
        }
        
        // Return a generic message in production, or more details in development
        if (app()->environment('production')) {
            return 'A database error occurred during registration. Please try again.';
        } else {
            return 'Database error: ' . $exception->getMessage();
        }
    }

    /**
     * Create a success response
     *
     * @param array $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    private function success($data = [], string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Create an error response
     *
     * @param string $message
     * @param string $errorCode
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    private function error(string $message, string $errorCode = 'ERROR', int $statusCode = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'code' => $errorCode
        ], $statusCode);
    }
    
    /**
     * Generate a random 6-digit activation code
     *
     * @return string
     */
    private function getActivationCode(): string
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send OTP code to the user's phone number
     *
     * @param string $phoneNumber
     * @param string $code
     * @return bool
     */
    private function sendOtp(string $phoneNumber, string $code): bool
    {
        try {
            return $this->verificationService->sendVerificationCode($phoneNumber);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
} 