<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferStatus extends Model
{
    protected $table = 'offer_status';
    
    public $timestamps = false;
    
    protected $fillable = ['name', 'slug', 'description'];

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }
} 