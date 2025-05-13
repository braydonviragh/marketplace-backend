<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MediaService
{
    private string $disk;
    private bool $canOptimize;

    public function __construct()
    {
        // Use the configured filesystem disk from config instead of determining based on environment
        $this->disk = config('filesystems.default', 's3');
        Log::info("MediaService initialized with disk: {$this->disk}");
        
        // Check if image optimization is possible
        $this->canOptimize = extension_loaded('gd') && class_exists('Intervention\Image\Facades\Image');
    }

    public function uploadMedia(Model $model, UploadedFile $file, array $options = []): Media
    {
        $options = array_merge([
            'is_primary' => false,
            'order' => 0,
            'metadata' => [],
            'optimize' => true,
        ], $options);

        // Only optimize if possible and requested
        if ($this->canOptimize && $options['optimize']) {
            try {
                $file = $this->optimizeImage($file);
            } catch (\Exception $e) {
                Log::warning('Image optimization failed: ' . $e->getMessage());
                // Continue without optimization
            }
        }

        // Get model type in lowercase
        $modelType = Str::lower(class_basename($model));
        
        // Set the base folder based on model type - using dashes instead of spaces
        $baseFolder = match($modelType) {
            'product' => 'product-images',
            'user' => 'user-images',
            default => $modelType . '-images',
        };
        
        // Generate path based on model type and ID
        $filePath = sprintf(
            '%s/%s/%s.%s',
            $baseFolder,
            $model->id,
            Str::uuid(),
            $file->getClientOriginalExtension()
        );

        // Store the file with public read access
        try {
            Log::info("Attempting to upload file to {$this->disk} disk at path: {$filePath}");
            
            // If using S3, set the visibility to public
            if ($this->disk === 's3') {
                Storage::disk($this->disk)->put($filePath, file_get_contents($file), [
                    'visibility' => 'public',
                    'ContentType' => $file->getMimeType(),
                ]);
            } else {
                Storage::disk($this->disk)->put($filePath, file_get_contents($file));
            }
            
            Log::info("File uploaded successfully to {$this->disk} disk");
        } catch (\Exception $e) {
            Log::error("Failed to upload file to {$this->disk} disk: " . $e->getMessage());
            throw $e;
        }

        // Create media record
        return $model->media()->create([
            'disk' => $this->disk,
            'path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'order' => $options['order'],
            'is_primary' => $options['is_primary'],
            'metadata' => array_merge($options['metadata'], [
                'optimized' => $this->canOptimize && $options['optimize'],
            ]),
        ]);
    }

    private function optimizeImage(UploadedFile $file)
    {
        if (!$this->canOptimize) {
            return $file;
        }

        $image = \Intervention\Image\Facades\Image::make($file);

        // Resize if too large (max 2000px width/height)
        if ($image->width() > 2000 || $image->height() > 2000) {
            $image->resize(2000, 2000, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Optimize quality
        $image->save(null, 80);

        return $file;
    }

    public function deleteMedia(Media $media): bool
    {
        Storage::disk($media->disk)->delete($media->path);
        return $media->delete();
    }
} 