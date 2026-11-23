<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the products with their hourly rates.
     */
    public function run(): void
    {
        $products = [
            'Consultancy' => 100,
            'Development' => 90,
            'Support' => 75,
            'Other' => 50,
        ];

        foreach ($products as $name => $hourlyRate) {
            Product::updateOrCreate(['name' => $name], ['hourly_rate' => $hourlyRate]);
        }
    }
}
