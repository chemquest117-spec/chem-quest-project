<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Student management
            'view_students',
            'create_students',
            'edit_students',
            'delete_students',
            'block_students',
            'unblock_students',

            // Admin management
            'view_admins',
            'create_admins',
            'edit_admins',
            'delete_admins',

            // Role and permission management
            'manage_roles',
            'manage_permissions',

            // License management
            'view_license',
            'activate_license',
            'deactivate_license',

            // System settings
            'manage_system_settings',

            // Audit logs
            'view_audit_logs',
        ];

        foreach ($permissions as $permission) {
            \App\Models\Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
