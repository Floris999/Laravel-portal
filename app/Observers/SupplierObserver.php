<?php

namespace App\Observers;

use App\Models\Supplier;
use App\Services\GeocodingService;

class SupplierObserver
{
    /**
     * Handle the Supplier "created" event.
     */
    public function created(Supplier $supplier): void
    {
        $this->geocodeIfNeeded($supplier);
    }

    /**
     * Handle the Supplier "updated" event.
     */
    public function updated(Supplier $supplier): void
    {
        if ($supplier->isDirty(['address', 'city', 'country', 'postal_code'])) {
            $this->geocodeIfNeeded($supplier);
        }
    }

    /**
     * Geocode supplier if coordinates are missing
     */
    private function geocodeIfNeeded(Supplier $supplier): void
    {
        if (! $supplier->latitude || ! $supplier->longitude) {
            $coordinates = GeocodingService::geocode(
                $supplier->address,
                $supplier->city,
                $supplier->country
            );

            if ($coordinates) {
                $supplier->update([
                    'latitude' => $coordinates['latitude'],
                    'longitude' => $coordinates['longitude'],
                ]);
            }
        }
    }
}
