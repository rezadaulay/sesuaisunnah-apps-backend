<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call role seeder first
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            EventSeeder::class,
            EventRegistrationSeeder::class,
            EbookSeeder::class,
            DonationSettingsSeeder::class,
        ]);
    }
}
