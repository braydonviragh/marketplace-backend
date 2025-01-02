<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class SmsService
{
    public function sendVerificationCode(string $phoneNumber, string $code): bool
    {
        try {
            // TODO: Implement your SMS provider here (Twilio, Vonage, etc.)
            // For now, we'll just log the code
            Log::info("Sending verification code: {$code} to {$phoneNumber}");
            
            return true;
        } catch (Exception $e) {
            Log::error("Failed to send SMS: {$e->getMessage()}");
            return false;
        }
    }
} 