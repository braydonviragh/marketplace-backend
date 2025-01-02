<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDetailedSize extends Model
{
    protected $fillable = [
        'user_id',
        'size_id',
        'waist_size_id',
        'number_size_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function letterSize(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'size_id');
    }

    public function waistSize(): BelongsTo
    {
        return $this->belongsTo(WaistSize::class);
    }

    public function numberSize(): BelongsTo
    {
        return $this->belongsTo(NumberSize::class);
    }
}