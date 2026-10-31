<?php

namespace Database\Seeders;

use App\Models\ShippingRate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'ar@amanreliablebusinessltd.com'],
            [
                'name' => 'Prime Cargo Admin',
                'password' => Hash::make('Primecargo9934!'),
                'role' => 'admin',
            ]
        );

        $rates = [
            ['direction' => 'uk_to_bd', 'service_type' => 'air_cargo', 'price_per_kg' => 6.50, 'min_weight_kg' => 1],
            ['direction' => 'uk_to_bd', 'service_type' => 'door_to_door', 'price_per_kg' => 7.50, 'min_weight_kg' => 1],
            ['direction' => 'uk_to_bd', 'service_type' => 'parcel', 'price_per_kg' => 5.50, 'min_weight_kg' => 0.5],
            ['direction' => 'bd_to_uk', 'service_type' => 'air_cargo', 'price_per_kg' => 7.00, 'min_weight_kg' => 1],
            ['direction' => 'bd_to_uk', 'service_type' => 'door_to_door', 'price_per_kg' => 8.00, 'min_weight_kg' => 1],
            ['direction' => 'bd_to_uk', 'service_type' => 'parcel', 'price_per_kg' => 6.00, 'min_weight_kg' => 0.5],
        ];

        foreach ($rates as $rate) {
            ShippingRate::firstOrCreate(
                ['direction' => $rate['direction'], 'service_type' => $rate['service_type']],
                $rate
            );
        }
    }
}
