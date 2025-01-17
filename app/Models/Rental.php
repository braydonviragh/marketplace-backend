<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rental extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'user_id',
        'rental_status_id',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function rentalStatus()
    {
        return $this->belongsTo(RentalStatus::class, 'rental_status_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereHas('rentalStatus', function($q) {
            $q->where('slug', 'active');
        });
    }

    public function scopePending($query)
    {
        return $query->whereHas('rentalStatus', function($q) {
            $q->where('slug', 'pending'); 
        });
    }
}