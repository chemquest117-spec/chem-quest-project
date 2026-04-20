<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminPermissions = [
            'view_students',
            'create_students',
            'edit_students',
            'delete_students',
            'block_students',
            'unblock_students',
            'view_audit_logs',
        ];

        $superAdminPermissions = [
            'view_students',
            'create_students',
            'edit_students',
            'delete_students',
            'block_students',
            'unblock_students',
            'view_admins',
            'create_admins',
            'edit_admins',
            'delete_admins',
            'manage_roles',
            'manage_permissions',
            'view_license',
            'activate_license',
            'deactivate_license',
            'manage_system_settings',
            'view_audit_logs',
        ];

        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        $superAdminRole = \App\Models\Role::where('name', 'super_admin')->first();

        if ($adminRole) {
            $adminRole->permissions()->sync(
                \App\Models\Permission::whereIn('name', $adminPermissions)->pluck('id')
            );
        }

        if ($superAdminRole) {
            $superAdminRole->permissions()->sync(
                \App\Models\Permission::whereIn('name', $superAdminPermissions)->pluck('id')
            );
        }
    }
}
