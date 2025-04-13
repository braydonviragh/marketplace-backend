<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $paymentDetails = is_string($this->payment_details) 
            ? json_decode($this->payment_details, true) 
            : $this->payment_details;
            
        return [
            'id' => $this->id,
            'rental_id' => $this->rental_id,
            'payer_id' => $this->payer_id,
            'payee_id' => $this->payee_id,
            'amount' => (float) $this->amount,
            'platform_fee' => (float) ($this->platform_fee ?? ($this->amount * 0.20)),
            'owner_amount' => (float) ($this->owner_amount ?? ($this->amount * 0.80)),
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'payment_intent_id' => $this->stripe_payment_intent_id,
            'refund_id' => $this->refund_id,
            'refunded_amount' => (float) ($this->refunded_amount ?? 0),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'payer' => $this->whenLoaded('payer', function () {
                return [
                    'id' => $this->payer->id,
                    'name' => $this->payer->name,
                    'email' => $this->payer->email,
                ];
            }),
            'payee' => $this->whenLoaded('payee', function () {
                return [
                    'id' => $this->payee->id,
                    'name' => $this->payee->name,
                    'email' => $this->payee->email,
                ];
            }),
            'rental' => $this->whenLoaded('rental', function () {
                return [
                    'id' => $this->rental->id,
                    'start_date' => $this->rental->start_date,
                    'end_date' => $this->rental->end_date,
                    'status' => $this->rental->status,
                    'product' => $this->rental->product ? [
                        'id' => $this->rental->product->id,
                        'name' => $this->rental->product->name,
                    ] : null,
                ];
            }),
            'payment_details' => $paymentDetails,
        ];
    }
} 