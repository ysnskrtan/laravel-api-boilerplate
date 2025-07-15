<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User; // Don't forget to import User model

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'create posts']);
        Permission::firstOrCreate(['name' => 'edit posts']);
        Permission::firstOrCreate(['name' => 'delete posts']);
        Permission::firstOrCreate(['name' => 'view reports']);
        Permission::firstOrCreate(['name' => 'access admin panel']);
        Permission::firstOrCreate(['name' => 'manage orders']);
        Permission::firstOrCreate(['name' => 'view own orders']); // Example of a more specific permission

        // Create Roles and assign Permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all()); // Admin gets all permissions

        $editorRole = Role::firstOrCreate(['name' => 'editor']);
        $editorRole->givePermissionTo(['create posts', 'edit posts']);

        $clientRole = Role::firstOrCreate(['name' => 'client']);
        $clientRole->givePermissionTo(['view own orders']);

        $guestRole = Role::firstOrCreate(['name' => 'guest']); // For users not logged in, or with limited access

        // Create a default admin user and assign role
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'), // Change this in production!
            ]
        );
        $adminUser->assignRole('admin');

        // Create a default client user
        $clientUser = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Client User',
                'password' => bcrypt('password'), // Change this in production!
            ]
        );
        $clientUser->assignRole('client');

        // You can create more users and assign roles here
    }
}