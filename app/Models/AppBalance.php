<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppBalance extends Model
{
    protected $table = 'app_balance';
    
    public $timestamps = false;
    
    protected $fillable = [
        'balance',
        'last_updated_at'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'last_updated_at' => 'datetime'
    ];

    /**
     * Get the current app balance
     */
    public static function getCurrentBalance(): float
    {
        return static::first()->balance ?? 0.00;
    }

    /**
     * Add amount to balance
     */
    public static function addToBalance(float $amount): void
    {
        static::where('id', 1)->update([
            'balance' => $amount,
            'last_updated_at' => now()
        ]);
    }
} 