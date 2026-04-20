<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = \App\Models\User::firstOrCreate(
            ['email' => 'superadmin@chemtrack.com'],
            [
                'name' => 'Super Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('my-password-1234'),
                'role' => 'super_admin',
            ]
        );

        // Ensure role is set
        $superAdmin->role = 'super_admin';
        $superAdmin->save();
    }
}
