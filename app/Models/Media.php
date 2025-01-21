<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'disk',
        'path',
        'file_name',
        'mime_type',
        'file_size',
        'order',
        'is_primary',
        'metadata'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'order' => 'integer',
        'is_primary' => 'boolean',
        'metadata' => 'array'
    ];

    protected $appends = ['url'];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
} 