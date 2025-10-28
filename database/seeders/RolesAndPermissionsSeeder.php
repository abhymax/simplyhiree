<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Create All Necessary Roles ---
        // Using firstOrCreate prevents errors if you run the seeder multiple times.
        $superadminRole = Role::firstOrCreate(['name' => 'Superadmin']);
        Role::firstOrCreate(['name' => 'Manager']);
        Role::firstOrCreate(['name' => 'client']);
        Role::firstOrCreate(['name' => 'partner']);
        Role::firstOrCreate(['name' => 'candidate']);

        // --- Create the Default Superadmin User ---
        // IMPORTANT: Change this email to your own admin email address if different
        $superadminUser = User::firstOrCreate(
            ['email' => 'admin@simplyhiree.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'), // You can change this password
            ]
        );

        // Assign the 'Superadmin' role to the user
        $superadminUser->assignRole($superadminRole);
    }
}

