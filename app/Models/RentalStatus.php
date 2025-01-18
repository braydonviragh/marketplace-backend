<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalStatus extends Model
{
    protected $table = 'rental_status';
    
    public $timestamps = false;
    
    protected $fillable = ['name', 'slug', 'description'];

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }
} 