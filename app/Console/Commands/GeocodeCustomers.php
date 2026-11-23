<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\GeocodingService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:geocode-customers')]
#[Description('Geocode all customers with missing coordinates')]
class GeocodeCustomers extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $customers = Customer::whereNull('latitude')->orWhereNull('longitude')->get();

        if ($customers->isEmpty()) {
            $this->info('All customers are already geocoded.');
            return Command::SUCCESS;
        }

        $this->info("Geocoding {$customers->count()} customers...");

        foreach ($customers as $customer) {
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
                $this->line("✓ {$customer->name}");
            } else {
                $this->warn("✗ {$customer->name} (not found)");
            }

            // Rate limiting: Nominatim allows 1 request per second
            sleep(1);
        }

        $this->info('Geocoding complete!');
        return Command::SUCCESS;
    }
}
