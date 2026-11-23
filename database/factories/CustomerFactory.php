<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dutchCities = [
            ['city' => 'Amsterdam', 'postal_code' => '1012 RE', 'address' => 'Raadhuisplein 1'],
            ['city' => 'Rotterdam', 'postal_code' => '3011 AB', 'address' => 'Coolsingel 40'],
            ['city' => 'Utrecht', 'postal_code' => '3511 LW', 'address' => 'Vredenburg 80'],
            ['city' => 'The Hague', 'postal_code' => '2595 AA', 'address' => 'Plein 1945 42'],
            ['city' => 'Eindhoven', 'postal_code' => '5611 AB', 'address' => 'Stadhuisplein 1'],
            ['city' => 'Groningen', 'postal_code' => '9711 ME', 'address' => 'Grote Markt 33'],
            ['city' => 'Maastricht', 'postal_code' => '6211 CX', 'address' => 'Onze Lieve Vrouweplein 1'],
            ['city' => 'Leiden', 'postal_code' => '2311 BK', 'address' => 'Rapenburg 25'],
            ['city' => 'Haarlem', 'postal_code' => '2011 BZ', 'address' => 'Grote Markt 16'],
            ['city' => 'Alkmaar', 'postal_code' => '1811 KB', 'address' => 'Waagplein 1'],
        ];

        $location = fake()->randomElement($dutchCities);

        return [
            'name' => fake()->company(),
            'kvk' => fake()->numerify('########'),
            'administration_email' => fake()->companyEmail(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('06########'),
            'customer_since' => fake()->dateTimeBetween('-5 years')->format('Y-m-d'),
            'address' => $location['address'],
            'postal_code' => $location['postal_code'],
            'city' => $location['city'],
            'country' => 'Netherlands',
            'logo_path' => null,
            'active' => fake()->boolean(80),
        ];
    }
}
