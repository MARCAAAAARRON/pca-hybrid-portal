<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Artisan;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure Roles Exist
        $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);

        // Revoke all existing permissions to start fresh and avoid carryover
        $superadminRole->syncPermissions([]);
        $adminRole->syncPermissions([]);
        $managerRole->syncPermissions([]);
        $supervisorRole->syncPermissions([]);

        // 2. Generate permissions via Filament Shield FIRST
        Artisan::call('shield:generate', ['--all' => true]);

        // 3. Superadmin gets EVERYTHING unconditionally
        $allPermissions = Permission::all();
        $superadminRole->syncPermissions($allPermissions);

        // 3. Define module groups
        $adminOnlyModules = ['audit::log']; // Admin: audit logs only
        $fieldDataModules = [
            'hybrid::distribution',
            'hybridization::record',
            'monthly::harvest',
            'nursery::operation',
            'pollen::production',
            'terminal',
        ];

        // Pages/widgets that all operational roles should access
        $sharedPermissions = [
            'page_MyProfilePage',
            'widget_StatsOverviewWidget',
            'widget_MonthlyProductionChart',
        ];

        // 4. Assign permissions to admin/manager/supervisor
        foreach ($allPermissions as $permission) {
            $name = $permission->name;

            // A. Shared pages/widgets - all operational roles
            if (in_array($name, $sharedPermissions)) {
                $adminRole->givePermissionTo($permission);
                $managerRole->givePermissionTo($permission);
                $supervisorRole->givePermissionTo($permission);
                continue;
            }

            // B. Admin-only modules (audit logs)
            foreach ($adminOnlyModules as $module) {
                if (str_ends_with($name, "_{$module}") || str_ends_with($name, "::{$module}")) {
                    $adminRole->givePermissionTo($permission);
                }
            }

            // C. Field-data modules - admin, manager, supervisor all get
            // the full permission set. Ownership/status restrictions
            // (draft-only editing, field-site or created_by scoping)
            // are enforced in the model Policies, not here.
            foreach ($fieldDataModules as $module) {
                if (str_ends_with($name, "_{$module}") || str_ends_with($name, "::{$module}")) {
                    $adminRole->givePermissionTo($permission);
                    $managerRole->givePermissionTo($permission);
                    $supervisorRole->givePermissionTo($permission);
                }
            }

            // D. Reports - manager/admin view-only, supervisor full CRUD
            // (supervisor ownership enforced in ReportPolicy via generated_by)
            if (str_ends_with($name, '_report')) {
                if (in_array($name, ['view_report', 'view_any_report'])) {
                    $adminRole->givePermissionTo($permission);
                    $managerRole->givePermissionTo($permission);
                    $supervisorRole->givePermissionTo($permission);
                } elseif (in_array($name, ['create_report', 'update_report', 'delete_report', 'delete_any_report'])) {
                    $supervisorRole->givePermissionTo($permission);
                }
            }
        }

        // 5. Assign roles to seeded users based on their 'role' column
        // (Note: the User model's saved() hook also does this automatically
        // on create/update, so this is mainly a safety net / re-sync.)
        $users = User::all();
        foreach ($users as $user) {
            if ($user->role) {
                $user->syncRoles([$user->role]);
            }
        }
    }
}