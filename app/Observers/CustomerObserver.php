<?php

namespace App\Observers;

use App\Models\Customer;
use App\Services\GeocodingService;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        $this->geocodeIfNeeded($customer);
    }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        // Re-geocode if address/city/country changed
        if ($customer->isDirty(['address', 'city', 'country', 'postal_code'])) {
            $this->geocodeIfNeeded($customer);
        }
    }

    /**
     * Geocode customer if coordinates are missing
     */
    private function geocodeIfNeeded(Customer $customer): void
    {
        if (! $customer->latitude || ! $customer->longitude) {
            $coordinates = GeocodingService::geocode(
                $customer->address,
                $customer->city,
                $customer->country
            );

            if ($coordinates) {
                $customer->update([
                    'latitude' => $coordinates['latitude'],
                    'longitude' => $coordinates['longitude'],
                ]);
            }
        }
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "restored" event.
     */
    public function restored(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "force deleted" event.
     */
    public function forceDeleted(Customer $customer): void
    {
        //
    }
}
