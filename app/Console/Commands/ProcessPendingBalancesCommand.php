<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPendingBalances;
use Illuminate\Console\Command;

class ProcessPendingBalancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-pending-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending balances for completed rentals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching job to process pending balances...');
        ProcessPendingBalances::dispatch();
        $this->info('Job dispatched successfully.');
        
        return Command::SUCCESS;
    }
} 