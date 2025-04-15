<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PhoneVerificationService
{
    protected $testMode;
    protected $isLocalEnv;

    public function __construct()
    {
        // Default to test mode unless explicitly set to false in environment
        $this->testMode = env('TWILIO_TEST_MODE', true);
        
        // Check if we're in local environment
        $this->isLocalEnv = env('APP_ENV') === 'local';
        
        // Log the current mode
        Log::info("Phone verification service initialized in " . 
                 ($this->testMode ? "TEST" : "PRODUCTION") . " mode, " .
                 ($this->isLocalEnv ? "LOCAL" : "NON-LOCAL") . " environment");
        
        // Production would use Twilio client initialization here
        if (!$this->testMode) {
            // TODO: Uncomment this once in production with Twilio credentials
            // Initialize Twilio client for production
            // $this->twilioClient = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
            // $this->verifySid = env('TWILIO_VERIFY_SID');
        }
    }

    /**
     * Send a verification code to the specified phone number
     *
     * @param string $phoneNumber
     * @return bool
     */
    public function sendVerificationCode(string $phoneNumber): bool
    {
        $phoneNumber = $this->sanitizePhoneNumber($phoneNumber);
        
        try {
            // Local environment - always succeeds
            if ($this->isLocalEnv) {
                Log::info("Local environment: Verification code for {$phoneNumber} automatically sent (local environment)");
                return true;
            }
            
            // Test mode - always works and uses predictable codes
            if ($this->testMode) {
                // Generate a test code (for development)
                $testCode = '123456';
                Cache::put("verification_code_{$phoneNumber}", $testCode, 600); // 10 minutes
                Log::info("Test mode: Verification code for {$phoneNumber} is {$testCode}");
                return true;
            }
            
            // Production mode - would use Twilio Verify API
            // $verification = $this->twilioClient->verify->v2->services($this->verifySid)
            //     ->verifications
            //     ->create($phoneNumber, 'sms');
                
            // Log::info("Verification sent to {$phoneNumber}: {$verification->status}");
            // return $verification->status === 'pending';
            
            // Placeholder for production without Twilio setup
            Log::info("Production mode: Verification would be sent to {$phoneNumber}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send verification to {$phoneNumber}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Verify the code provided by the user
     *
     * @param string $phoneNumber
     * @param string $code
     * @return bool
     */
    public function verifyCode(string $phoneNumber, string $code): bool
    {
        $phoneNumber = $this->sanitizePhoneNumber($phoneNumber);
        
        try {
            // Local environment - always succeeds for easier testing
            if ($this->isLocalEnv) {
                Log::info("Local environment: Verification for {$phoneNumber} with code {$code} is automatically approved (local environment)");
                
                // Add the phone number to verified phones in session for registration
                $verifiedPhones = session('verified_phones', []);
                if (!in_array($phoneNumber, $verifiedPhones)) {
                    $verifiedPhones[] = $phoneNumber;
                    session(['verified_phones' => $verifiedPhones]);
                }
                
                return true;
            }
            
            // Test mode - bypass verification in development/testing
            if ($this->testMode) {
                // Check for a specific test code or cached code
                $testCode = Cache::get("verification_code_{$phoneNumber}", '123456');
                $isValid = $code === '123456' || $code === $testCode;
                
                Log::info("Test mode: Verification for {$phoneNumber} with code {$code} is " . 
                    ($isValid ? "valid" : "invalid"));
                
                return $isValid;
            }
            
            // Production mode - would use Twilio Verify API
            // $verification = $this->twilioClient->verify->v2->services($this->verifySid)
            //     ->verificationChecks
            //     ->create([
            //         'to' => $phoneNumber,
            //         'code' => $code
            //     ]);
                
            // Log::info("Verification check for {$phoneNumber}: {$verification->status}");
            // return $verification->status === 'approved';
            
            // Placeholder for production without Twilio setup
            Log::info("Production mode: Verification check would be performed for {$phoneNumber}");
            return $code === '123456'; // Default test code for demo
        } catch (\Exception $e) {
            Log::error("Failed to verify code for {$phoneNumber}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Sanitize phone number to E.164 format
     *
     * @param string $phoneNumber
     * @return string
     */
    private function sanitizePhoneNumber(string $phoneNumber): string
    {
        // Remove any non-digit characters except the leading +
        $phoneNumber = preg_replace('/[^\d+]/', '', $phoneNumber);
        
        // Ensure the number starts with +
        if (substr($phoneNumber, 0, 1) !== '+') {
            $phoneNumber = '+' . $phoneNumber;
        }
        
        return $phoneNumber;
    }
} 