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
        // If the disk is s3, make sure we use the correct S3 URL format
        if ($this->disk === 's3') {
            $s3BaseUrl = config('filesystems.disks.s3.url');
            if (!$s3BaseUrl) {
                $s3BaseUrl = 'https://' . config('filesystems.disks.s3.bucket') . '.s3.' . 
                             config('filesystems.disks.s3.region') . '.amazonaws.com';
            }
            
            return $s3BaseUrl . '/' . $this->path;
        }
        
        // For other disks, use Laravel's Storage URL generator
        return Storage::disk($this->disk)->url($this->path);
    }
} 