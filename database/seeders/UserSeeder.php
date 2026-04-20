<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('my-password-1234');

        $users = [
            [
                'name' => 'Admin Teacher',
                'email' => 'admin@chemtrack.com',
                'password' => $defaultPassword,
                'is_admin' => true,
                'email_verified_at' => now(),
                'streak' => 20,
                'total_points' => 200,
                'stars' => 20,
            ],
            [
                'name' => 'Ahmed Student',
                'email' => 'student@chemtrack.com',
                'password' => $defaultPassword,
                'is_admin' => false,
                'email_verified_at' => now(),
                'streak' => 3,
                'total_points' => 80,
                'stars' => 7,
            ],
            [
                'name' => 'Alaa Student',
                'email' => 'student2@chemtrack.com',
                'password' => $defaultPassword,
                'is_admin' => false,
                'email_verified_at' => now(),
                'streak' => 5,
                'total_points' => 100,
                'stars' => 10,
            ],
            [
                'name' => 'Mohamed Student',
                'email' => 'student3@chemtrack.com',
                'password' => $defaultPassword,
                'is_admin' => false,
                'email_verified_at' => now(),
                'streak' => 7,
                'total_points' => 150,
                'stars' => 13,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
