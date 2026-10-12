<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

/**
 * Give the modules built after the original permission set their own
 * permissions, so a manager's access to them can actually be ticked on and off.
 *
 * Until now Questionnaires, Assessment Results, Chats and Activity Logs had no
 * permission at all -- every manager could open them -- while Offer Letters,
 * Vendor Ratings and Advanced Analytics were bundled behind "View Billing
 * Data", so granting one meant granting the lot.
 *
 * Each new permission is granted to exactly the managers who can already reach
 * that module, so nobody's access changes when this runs. Tightening is then a
 * matter of unticking a box.
 */
return new class extends Migration
{
    /** new permission => the module's current gate, used to decide who keeps it */
    private const PLAN = [
        'view_assessments'        => null,                 // null = no gate today, so every manager
        'view_assessment_results' => null,
        'view_chats'              => null,
        'view_activity_logs'      => null,
        'manage_offer_letters'    => 'view_billing_data',
        'view_vendor_ratings'     => 'view_billing_data',
        'view_analytics'          => 'view_billing_data',
    ];

    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::transaction(function () {
            $managers = User::role('Manager')->get();

            foreach (self::PLAN as $name => $currentGate) {
                $permission = Permission::firstOrCreate(['name' => $name]);

                $keep = $currentGate === null
                    ? $managers
                    : $managers->filter(fn (User $m) => $m->can($currentGate));

                foreach ($keep as $manager) {
                    $manager->givePermissionTo($permission);
                }
            }

            if ($superadmin = Role::where('name', 'Superadmin')->first()) {
                $superadmin->syncPermissions(Permission::all());
            }
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Removing the permission rows drops the grants with them.
        Permission::whereIn('name', array_keys(self::PLAN))->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
