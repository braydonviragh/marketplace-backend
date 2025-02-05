<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Media;
use App\Repositories\ProductRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductService
{
    protected ProductRepository $productRepository;
    protected ImageManager $imageManager;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Compress and save image
     */
    protected function compressAndSaveImage($file, $filename): void
    {
        try {
            // Ensure the storage directory exists
            $directory = storage_path('app/public/products');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $image = $this->imageManager->read($file->getRealPath());
            
            // Get original file size in MB
            $originalSize = $file->getSize() / (1024 * 1024);
            
            // Base quality setting - start with 80% for files under 8MB
            $quality = 80;
            
            // For files over 8MB, reduce quality proportionally
            if ($originalSize > 8) {
                $quality = max(40, round(80 * (8 / $originalSize)));
            }
            
            // Resize if width or height is greater than 2000px while maintaining aspect ratio
            if ($image->width() > 2000 || $image->height() > 2000) {
                $image->scale(width: 2000);
            }
            
            // Compress image with calculated quality
            $image->toJpeg($quality)->save($directory . '/' . $filename);
        } catch (\Exception $e) {
            \Log::error('Error processing image: ' . $e->getMessage(), [
                'file' => $file,
                'filename' => $filename,
                'error' => $e
            ]);
            throw $e;
        }
    }

    public function getProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getFilteredProducts($filters, $perPage);
    }

    public function findProduct(int $id): Product
    {
        return $this->productRepository->findProductWithRelations($id);
    }

    public function createProduct(array $data): Product
    {
        // Create the product first
        $product = $this->productRepository->create($data);

        // Handle media files upload
        if (isset($data['media']) && is_array($data['media'])) {
            foreach ($data['media'] as $index => $mediaItem) {
                if (!empty($mediaItem['file']) && $mediaItem['file']->isValid()) {
                    $filename = Str::uuid() . '.jpg';
                    
                    // Compress and store the file
                    $this->compressAndSaveImage($mediaItem['file'], $filename);
                    
                    // Create media record
                    $product->media()->create([
                        'disk' => 'public',
                        'path' => 'products/' . $filename,
                        'file_name' => $filename,
                        'mime_type' => 'image/jpeg',
                        'file_size' => filesize(storage_path('app/public/products/' . $filename)),
                        'order' => $mediaItem['order'] ?? $index + 1,
                        'is_primary' => $index === 0,
                        'metadata' => [
                            'original_name' => $mediaItem['file']->getClientOriginalName(),
                            'extension' => 'jpg'
                        ]
                    ]);
                }
            }
        }

        return $product->fresh();
    }

    public function updateProduct(Product $product, array $data): Product
    {
        \Log::info('Updating product with data:', [
            'product_id' => $product->id,
            'media_count' => isset($data['media']) ? count($data['media']) : 0,
            'media_data' => $data['media'] ?? []
        ]);

        $this->productRepository->update($product, $data);

        // Get all current media IDs
        $currentMediaIds = $product->media->pluck('id')->toArray();
        $updatedMediaIds = [];

        if (isset($data['media']) && is_array($data['media'])) {
            foreach ($data['media'] as $index => $mediaItem) {
                if (!empty($mediaItem['id'])) {
                    // Handle existing media
                    $mediaModel = $product->media()->find($mediaItem['id']);
                    if ($mediaModel) {
                        $updatedMediaIds[] = $mediaModel->id;
                        
                        // Only update the order
                        if ($mediaModel->order !== $mediaItem['order']) {
                            \Log::info("Updating order for media {$mediaItem['id']}", [
                                'old_order' => $mediaModel->order,
                                'new_order' => $mediaItem['order']
                            ]);
                            $mediaModel->update(['order' => $mediaItem['order']]);
                        }
                    }
                } 
                // Handle new media
                elseif (!empty($mediaItem['file'])) {
                    $filename = Str::uuid() . '.jpg';
                    
                    // Save new file
                    $this->compressAndSaveImage($mediaItem['file'], $filename);
                    
                    // Check if product has any existing media
                    $hasPrimaryMedia = $product->media()->where('is_primary', true)->exists();
                    
                    $newMedia = $product->media()->create([
                        'disk' => 'public',
                        'path' => 'products/' . $filename,
                        'file_name' => $filename,
                        'mime_type' => 'image/jpeg',
                        'file_size' => filesize(storage_path('app/public/products/' . $filename)),
                        'order' => $mediaItem['order'],
                        'is_primary' => !$hasPrimaryMedia && empty($updatedMediaIds) && $index === 0, // Only set primary if no primary exists and it's first new media
                        'metadata' => [
                            'original_name' => $mediaItem['file']->getClientOriginalName(),
                            'extension' => 'jpg'
                        ]
                    ]);
                    
                    $updatedMediaIds[] = $newMedia->id;
                }
            }
        }
        return $product->fresh();
    }

    public function deleteProduct(Product $product): bool
    {
        // Delete associated media files
        foreach ($product->media as $media) {
            Storage::disk('public')->delete($media->path);
        }
        
        return $this->productRepository->delete($product);
    }
} 