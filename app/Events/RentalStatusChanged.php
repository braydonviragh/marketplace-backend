<?php

namespace App\Events;

use App\Models\Rental;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RentalStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Rental $rental,
        public string $newStatus
    ) {}
} 