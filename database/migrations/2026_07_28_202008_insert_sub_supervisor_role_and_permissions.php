<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        $role = Role::firstOrCreate(['name' => 'sub_supervisor', 'guard_name' => 'web']);

        $permissions = [
            'view_any_hybrid::distribution',
            'view_hybrid::distribution',
            'create_hybrid::distribution',
            'update_hybrid::distribution',
            'delete_hybrid::distribution',
            
            'view_any_nursery::operation',
            'view_nursery::operation',
            'create_nursery::operation',
            'update_nursery::operation',
            'delete_nursery::operation',

            'view_any_terminal',
            'view_terminal',
            'create_terminal',
            'update_terminal',
            'delete_terminal',

            'page_MyProfile',
            'page_ReportsDashboard',
        ];

        foreach ($permissions as $permName) {
            $permission = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            $role->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        $role = Role::where('name', 'sub_supervisor')->where('guard_name', 'web')->first();
        if ($role) {
            $role->delete();
        }
    }
};
