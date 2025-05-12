<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    private string $disk;
    private bool $canOptimize;

    public function __construct()
    {
        // Use S3 when FILESYSTEM_DISK is set to 's3', otherwise use 'public'
        $this->disk = config('filesystems.default') === 's3' ? 's3' : 'public';
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
                \Log::warning('Image optimization failed: ' . $e->getMessage());
                // Continue without optimization
            }
        }

        // Generate path based on model type and ID
        $modelType = Str::lower(class_basename($model));
        $filePath = sprintf(
            '%s/%s/%s.%s',
            $modelType,
            $model->id,
            Str::uuid(),
            $file->getClientOriginalExtension()
        );

        // Store the file
        Storage::disk($this->disk)->put($filePath, file_get_contents($file));

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