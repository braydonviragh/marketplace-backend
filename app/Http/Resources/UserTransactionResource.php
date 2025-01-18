<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        $baseData = [
            'id' => $this->id,
            'amount' => $this->amount,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'description' => $this->getTransactionDescription()
        ];

        // Add rental details only for rental-related transactions
        if ($this->type === 'add' && $this->rental) {
            $baseData['rental'] = [
                'id' => $this->rental->id,
                'start_date' => $this->rental->start_date,
                'end_date' => $this->rental->end_date,
                'product' => [
                    'id' => $this->rental->product->id,
                    'title' => $this->rental->product->title,
                    'price' => $this->rental->product->price,
                ]
            ];
        }

        return $baseData;
    }

    private function getTransactionDescription(): string
    {
        if ($this->type === 'add' && $this->rental) {
            return "Rental payment received for " . $this->rental->product->title;
        }

        if ($this->type === 'remove') {
            return "Withdrawal to bank account";
        }

        return "Transaction";
    }
} 