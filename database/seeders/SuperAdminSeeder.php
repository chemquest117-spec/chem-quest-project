<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@chemtrack.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('my-password-1234'),
                'role' => 'super_admin',
            ]
        );

        // Ensure role is set
        $superAdmin->role = 'super_admin';
        $superAdmin->save();
    }
}
