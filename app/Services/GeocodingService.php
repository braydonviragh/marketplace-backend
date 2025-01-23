<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    private string $apiKey;
    
    public function __construct()
    {
        $this->apiKey = config('services.google.maps_api_key');
        
        if (!$this->apiKey) {
            throw new \RuntimeException('Google Maps API key is not configured');
        }
    }

    public function getCoordinatesFromPostalCode(string $postalCode): ?array
    {
        // Normalize postal code format (remove spaces, uppercase)
        $postalCode = strtoupper(str_replace(' ', '', $postalCode));
        
        // Cache coordinates for 30 days to reduce API calls
        $cacheKey = "postal_code_coordinates_{$postalCode}";
        
        return Cache::remember($cacheKey, now()->addDays(30), function () use ($postalCode) {
            try {
                $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'address' => $postalCode . ' Canada',
                    'key' => $this->apiKey,
                    'region' => 'ca', // Bias results to Canada
                    'components' => 'country:CA' // Restrict results to Canada
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (!empty($data['results'])) {
                        $result = $data['results'][0];
                        $location = $result['geometry']['location'];
                        
                        // Also extract city and province if available
                        $city = null;
                        $province = null;
                        
                        foreach ($result['address_components'] as $component) {
                            if (in_array('locality', $component['types'])) {
                                $city = $component['long_name'];
                            }
                            if (in_array('administrative_area_level_1', $component['types'])) {
                                $province = $component['long_name'];
                            }
                        }

                        return [
                            'latitude' => $location['lat'],
                            'longitude' => $location['lng'],
                            'city' => $city,
                            'province' => $province
                        ];
                    }
                }

                Log::warning('Geocoding failed for postal code: ' . $postalCode, [
                    'response' => $response->json()
                ]);

                return null;

            } catch (\Exception $e) {
                Log::error('Geocoding error: ' . $e->getMessage(), [
                    'postal_code' => $postalCode
                ]);
                return null;
            }
        });
    }
} 