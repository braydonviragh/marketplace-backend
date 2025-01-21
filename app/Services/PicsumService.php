<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PicsumService
{
    public function getRandomImage(int $width = 600, int $height = 800): ?UploadedFile
    {
        $randomId = rand(1, 1000);
        $url = "https://picsum.photos/id/{$randomId}/{$width}/{$height}";
        
        try {
            // Get image content
            $response = Http::get($url);
            if (!$response->successful()) {
                return null;
            }

            // Create a temporary file
            $tempPath = tempnam(sys_get_temp_dir(), 'picsum');
            file_put_contents($tempPath, $response->body());

            // Create UploadedFile instance
            return new UploadedFile(
                $tempPath,
                "picsum-{$randomId}.jpg",
                'image/jpeg',
                null,
                true
            );
        } catch (\Exception $e) {
            \Log::error('Failed to fetch Picsum image: ' . $e->getMessage());
            return null;
        }
    }
} 