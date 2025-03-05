<?php

namespace App\Listeners;

use App\Events\OfferStatusChanged;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleOfferStatusChanged implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OfferStatusChanged $event): void
    {
        if ($event->newStatus === 'accepted') {
            // Create notification for the user who made the offer
            Notification::create([
                'user_id' => $event->offer->user_id,
                'title' => 'Offer Accepted',
                'message' => "Your offer for {$event->offer->product->name} has been accepted!",
                'type' => 'offer_accepted',
                'data' => [
                    'offer_id' => $event->offer->id,
                    'product_id' => $event->offer->product_id,
                ]
            ]);

            // TODO: Send SMS notification
            // $phone = $event->offer->user->phone;
            // if ($phone) {
            //     SMS::send($phone, "Your offer for {$event->offer->product->name} has been accepted!");
            // }
        }
    }
} 