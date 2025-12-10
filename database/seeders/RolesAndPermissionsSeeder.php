<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Granular Permissions
        $permissions = [
            'access_admin_panel',
            'manage_sub_admins',   // Only Superadmin
            'manage_clients',      // Assigning clients to others
            'impersonate_clients', // The "Mimic" capability
            
            // Optional Data Access
            'view_partner_data',
            'view_application_data',
            'view_pending_jobs',
            'view_candidate_data',
            'view_interview_data',
            'view_billing_data',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Create Roles
        $superadminRole = Role::firstOrCreate(['name' => 'Superadmin']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager']); // Sub-Admin
        Role::firstOrCreate(['name' => 'client']);
        Role::firstOrCreate(['name' => 'partner']);
        Role::firstOrCreate(['name' => 'candidate']);

        // 3. Assign ALL permissions to Superadmin
        $superadminRole->syncPermissions(Permission::all());

        // 4. Manager starts with basic access (extra permissions given per user via UI)
        $managerRole->syncPermissions(['access_admin_panel', 'impersonate_clients']);

        // 5. Ensure Superadmin User exists
        $superadminUser = User::firstOrCreate(
            ['email' => 'admin@simplyhiree.com'],
            ['name' => 'Super Admin', 'password' => bcrypt('password')]
        );
        $superadminUser->assignRole($superadminRole);
    }
}