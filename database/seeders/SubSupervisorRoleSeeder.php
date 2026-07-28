<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SubSupervisorRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'sub_supervisor', 'guard_name' => 'web']);

        $permissions = [
            // Hybrid Distribution
            'view_any_hybrid::distribution',
            'view_hybrid::distribution',
            'create_hybrid::distribution',
            'update_hybrid::distribution',
            'delete_hybrid::distribution',
            
            // Nursery Operation
            'view_any_nursery::operation',
            'view_nursery::operation',
            'create_nursery::operation',
            'update_nursery::operation',
            'delete_nursery::operation',

            // Terminal Report
            'view_any_terminal',
            'view_terminal',
            'create_terminal',
            'update_terminal',
            'delete_terminal',

            // Common Pages
            'page_MyProfile',
            'page_ReportsDashboard',
        ];

        foreach ($permissions as $permName) {
            $permission = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            $role->givePermissionTo($permission);
        }
    }
}
