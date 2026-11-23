<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * Geocode an address using Nominatim (OpenStreetMap)
     * Returns [latitude, longitude] or null if not found
     */
    public static function geocode(string $address, string $city, string $country): ?array
    {
        try {
            $query = "{$address}, {$city}, {$country}";
            
            $response = Http::timeout(10)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $query,
                'format' => 'json',
                'limit' => 1,
            ]);

            if ($response->successful() && $response->json()) {
                $result = $response->json()[0];
                return [
                    'latitude' => (float) $result['lat'],
                    'longitude' => (float) $result['lon'],
                ];
            }
        } catch (\Exception $e) {
            Log::warning("Geocoding failed for {$address}, {$city}, {$country}: {$e->getMessage()}");
        }

        return null;
    }
}
