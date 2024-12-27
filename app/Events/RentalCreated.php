<?php

namespace App\Events;

use App\Models\Rental;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RentalCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Rental $rental
    ) {}
} 