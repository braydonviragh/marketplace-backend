<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalConfirmation extends Model
{
    protected $fillable = ['rental_id', 'user_id', 'type'];

    protected $table = 'rental_confirmation';

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 