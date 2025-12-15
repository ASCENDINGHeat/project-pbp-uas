<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class productsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $vendorIds = DB::table('vendors')->pluck('id')->toArray();

        foreach ($vendorIds as $vendorId) {
            // Create 5 products per vendor
            for ($i = 0; $i < 5; $i++) {
                DB::table('products')->insert([
                    'vendor_id' => $vendorId, // Matches your migration column name
                    'title' => $faker->productName,
                    'price' => $faker->randomFloat(2, 10, 500),
                    'stock_quantity' => $faker->numberBetween(0, 100),
                    'details' => json_encode([
                        'color' => $faker->colorName,
                        'material' => $faker->word
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
