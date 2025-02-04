<?php

namespace App\Listeners;

use App\Events\OfferCreated;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleOfferCreated implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OfferCreated $event): void
    {
        // Create notification for the product owner
        Notification::create([
            'user_id' => $event->offer->product->user_id,
            'title' => 'New Offer Received',
            'message' => "You have received a new offer for {$event->offer->product->name}",
            'type' => 'offer_received',
            'data' => [
                'offer_id' => $event->offer->id,
                'product_id' => $event->offer->product_id,
                'user_id' => $event->offer->user_id,
            ]
        ]);
    }
} 