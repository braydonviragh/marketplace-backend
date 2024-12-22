<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rental_id',
        'reviewer_id',
        'reviewee_id',
        'rating',
        'comment',
        'criteria_ratings',
        'is_approved',
        'moderation_notes',
        'owner_response'
    ];

    protected $casts = [
        'rating' => 'integer',
        'criteria_ratings' => 'array',
        'is_approved' => 'boolean',
        'moderated_at' => 'datetime',
        'response_at' => 'datetime'
    ];

    // Relationships
    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    // Review Methods
    public function addOwnerResponse(string $response)
    {
        $this->update([
            'owner_response' => $response,
            'response_at' => now()
        ]);
    }

    public function moderate(bool $approved, ?string $notes = null)
    {
        $this->update([
            'is_approved' => $approved,
            'moderation_notes' => $notes,
            'moderated_at' => now()
        ]);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->whereNull('moderated_at');
    }
} 