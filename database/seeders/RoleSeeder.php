<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Event management
            'view events',
            'create events',
            'edit events',
            'delete events',
            'manage event registrations',
            
            // E-book management
            'view ebooks',
            'create ebooks',
            'edit ebooks',
            'delete ebooks',
            'manage ebook access',
            
            // Donation management
            'view donations',
            'create donations',
            'edit donations',
            'delete donations',
            
            // System management
            'view audit logs',
            'manage roles',
            'manage permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $eventManagerRole = Role::firstOrCreate(['name' => 'event_manager']);
        $memberRole = Role::firstOrCreate(['name' => 'member']);

        // Assign permissions to admin role (all permissions)
        $adminRole->givePermissionTo(Permission::all());

        // Assign permissions to event manager role
        $eventManagerRole->givePermissionTo([
            'view users',
            'view events',
            'create events',
            'edit events',
            'manage event registrations',
            'view ebooks',
            'view donations',
        ]);

        // Assign permissions to member role
        $memberRole->givePermissionTo([
            'view events',
            'view ebooks',
        ]);
    }
}
