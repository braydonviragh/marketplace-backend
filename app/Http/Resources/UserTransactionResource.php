<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        $baseData = [
            'id' => $this->id,
            'amount' => $this->amount,
            'type' => $this->type,
            'created_at' => $this->created_at,
        ];

        // Add rental and related data only for rental-related transactions
        if ($this->rental) {
            $product = $this->rental->offer->product;
            $media = $product->media->first();
            
            $baseData['rental'] = [
                'id' => $this->rental->id,
                'offer' => [
                    'id' => $this->rental->offer->id,
                    'start_date' => $this->rental->offer->start_date,
                    'end_date' => $this->rental->offer->end_date,
                    'product' => [
                        'id' => $product->id,
                        'title' => $product->title,
                        'price' => $product->price,
                        'description' => $product->description,
                        'thumbnail' => $media ? url($media->original_url) : null,
                    ],
                ],
                'status' => $this->rental->rentalStatus->name,
            ];

            // Add transaction description based on type and related data
            $baseData['description'] = $this->type === 'add' 
                ? "Rental payment received for {$product->title}"
                : "Rental payment refund for {$product->title}";
        } else {
            // For non-rental transactions (like withdrawals)
            $baseData['description'] = $this->type === 'remove' 
                ? "Withdrawal to bank account"
                : "Other transaction";
        }

        return $baseData;
    }
} 