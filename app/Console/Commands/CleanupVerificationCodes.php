<?php

namespace App\Console\Commands;

use App\Models\VerificationCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupVerificationCodes extends Command
{
    protected $signature = 'verification:cleanup {--days=7}';
    protected $description = 'Clean up expired verification codes';

    public function handle()
    {
        $days = $this->option('days');
        
        try {
            $count = VerificationCode::where('expires_at', '<', now())
                ->orWhere('created_at', '<', now()->subDays($days))
                ->delete();

            $this->info("Successfully deleted {$count} expired verification codes.");
            Log::info("Cleanup: Deleted {$count} expired verification codes.");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error cleaning up verification codes: {$e->getMessage()}");
            Log::error("Verification code cleanup failed: {$e->getMessage()}");
            
            return Command::FAILURE;
        }
    }
} 