<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Offer;
use App\Models\RentalStatus;
use App\Models\Rental;
use App\Services\RentalService;
use Carbon\Carbon;

class UpdateRentalStatuses extends Command
{
    protected $signature = 'rentals:update-statuses';
    protected $description = 'Update rental statuses based on offer start and end dates';

    public function __construct(
        private RentalService $rentalService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $today = Carbon::today();

        // Find rentals with offers starting today and update to active
        $startingRentals = Rental::with('offer')
            ->whereHas('offer', function ($query) use ($today) {
                $query->whereDate('start_date', $today);
            })
            ->where('rental_status_id', RentalStatus::where('slug', 'pending')->first()->id)
            ->get();

        foreach ($startingRentals as $rental) {
            try {
                // This will handle rental confirmation, user transactions, and status update
                $this->rentalService->confirmRental($rental);
                $this->info("Rental ID {$rental->id} for Offer ID {$rental->offer->id} activated and confirmed.");
            } catch (\Exception $e) {
                $this->error("Failed to activate Rental ID {$rental->id}: " . $e->getMessage());
            }
        }

        $this->info(count($startingRentals) . " rentals processed for activation.");

        // Find rentals with offers ending today and update to completed
        $endingRentals = Rental::with('offer')
            ->whereHas('offer', function ($query) use ($today) {
                $query->whereDate('end_date', $today);
            })
            ->where('rental_status_id', RentalStatus::where('slug', 'active')->first()->id)
            ->get();

        foreach ($endingRentals as $rental) {
            try {
                $rental->update([
                    'rental_status_id' => RentalStatus::where('slug', 'completed')->first()->id
                ]);
                $this->info("Rental ID {$rental->id} for Offer ID {$rental->offer->id} completed.");
            } catch (\Exception $e) {
                $this->error("Failed to complete Rental ID {$rental->id}: " . $e->getMessage());
            }
        }

        $this->info(count($endingRentals) . " rentals completed.");
    }
} 