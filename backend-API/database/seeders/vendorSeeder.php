<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class vendorSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Get user IDs to attach vendors to
        $userIds = DB::table('users')->pluck('id')->toArray();

        foreach ($userIds as $userId) {
            // 50% chance a user is also a vendor
            if (rand(0, 1)) {
                DB::table('vendors')->insert([
                    'user_id' => $userId,
                    'store_name' => $faker->company . ' Store',
                    'store_description' => $faker->catchPhrase,
                    'commission_rate' => 5.00,
                    'balance' => $faker->randomFloat(2, 0, 1000),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
