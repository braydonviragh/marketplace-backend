<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppTransaction extends Model
{
    protected $fillable = [
        'rental_id',
        'amount'
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }
} 