<?php

namespace App\Services;

use App\Models\Listing;
use App\Repositories\ListingRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ListingService
{
    protected ListingRepository $repository;
    
    public function __construct(ListingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getListings(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function findListing(int $id): Listing
    {
        $listing = $this->repository->find($id);
        
        if (!$listing) {
            throw new ModelNotFoundException("Listing not found");
        }
        
        $listing->incrementViews();
        return $listing->load(['owner', 'category', 'images']);
    }

    public function createListing(array $data, array $images = []): Listing
    {
        return DB::transaction(function () use ($data, $images) {
            $listing = $this->repository->create($data);
            
            if (!empty($images)) {
                $this->handleImageUploads($listing, $images);
            }
            
            return $listing->load(['owner', 'category', 'images']);
        });
    }

    public function updateListing(Listing $listing, array $data, array $images = []): Listing
    {
        return DB::transaction(function () use ($listing, $data, $images) {
            $listing = $this->repository->update($listing, $data);
            
            if (!empty($images)) {
                $this->handleImageUploads($listing, $images);
            }
            
            return $listing->load(['owner', 'category', 'images']);
        });
    }

    public function deleteListing(Listing $listing): bool
    {
        return DB::transaction(function () use ($listing) {
            // Delete images from storage
            foreach ($listing->images as $image) {
                Storage::disk('s3')->delete($image->path);
                $image->delete();
            }
            
            return $this->repository->delete($listing);
        });
    }

    public function searchNearby(float $latitude, float $longitude, float $radius = 25, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->searchNearby($latitude, $longitude, $radius, $filters);
    }

    protected function handleImageUploads(Listing $listing, array $images): void
    {
        foreach ($images as $image) {
            $path = $image->store('listings/' . $listing->id, 's3');
            
            $listing->images()->create([
                'path' => $path,
                'type' => $image->getMimeType(),
                'size' => $image->getSize(),
                'order' => $listing->images()->count() + 1
            ]);
        }
    }
} 