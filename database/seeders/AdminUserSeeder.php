<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'daulayreza@gmail.com'],
            [
                'name' => 'Reza Daulay',
                'password' => bcrypt('password'),
                'phone' => '08123456789',
                'gender' => 'male',
            ]
        );

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        $admin->assignRole($adminRole);

        // Create event manager user
        $eventManager = User::firstOrCreate(
            ['email' => 'event@sesuaisunnah.com'],
            [
                'name' => 'Event Manager',
                'password' => bcrypt('password'),
                'phone' => '08123456788',
                'gender' => 'male',
            ]
        );

        // Assign event manager role
        $eventManagerRole = Role::where('name', 'event_manager')->first();
        $eventManager->assignRole($eventManagerRole);

        // Create sample member user
        $member = User::firstOrCreate(
            ['email' => 'member@sesuaisunnah.com'],
            [
                'name' => 'Sample Member',
                'password' => bcrypt('password'),
                'phone' => '08123456787',
                'gender' => 'female',
            ]
        );

        // Assign member role
        $memberRole = Role::where('name', 'member')->first();
        $member->assignRole($memberRole);
    }
}
