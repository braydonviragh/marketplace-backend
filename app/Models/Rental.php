<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rental extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'renter_id',
        'owner_id',
        'listing_id',
        'start_date',
        'end_date',
        'total_price',
        'owner_earnings',
        'platform_fee',
        'status',
        'picked_up_at',
        'returned_at',
        'cancellation_reason',
        'status_history'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'picked_up_at' => 'datetime',
        'returned_at' => 'datetime',
        'status_history' => 'array',
        'total_price' => 'decimal:2',
        'owner_earnings' => 'decimal:2',
        'platform_fee' => 'decimal:2'
    ];

    // Relationships
    public function renter()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Status Management
    public function updateStatus(string $newStatus, ?string $reason = null)
    {
        $oldStatus = $this->status;
        $this->status = $newStatus;
        
        // Track status history
        $this->status_history = array_merge($this->status_history ?? [], [[
            'from' => $oldStatus,
            'to' => $newStatus,
            'reason' => $reason,
            'timestamp' => now()->toIso8601String()
        ]]);
        
        $this->save();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['confirmed', 'in_progress']);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'confirmed')
                    ->where('start_date', '>', now());
    }
} 