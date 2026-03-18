<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
     public function run(): void
     {
          User::create([
               'name' => 'Admin Teacher',
               'email' => 'admin@chemtrack.com',
               'password' => Hash::make('password'),
               'is_admin' => true,
               'email_verified_at' => now(),
               'streak' => 20,
               'total_points' => 200,
               'stars' => 20,
          ]);

          User::create([
               'name' => 'Ahmed Student',
               'email' => 'student@chemtrack.com',
               'password' => Hash::make('password'),
               'is_admin' => false,
               'email_verified_at' => now(),
               'streak' => 3,
               'total_points' => 80,
               'stars' => 7,
          ]);

          User::create([
               'name' => 'Alaa Teacher',
               'email' => 'student2@chemtrack.com',
               'password' => Hash::make('password'),
               'is_admin' => false,
               'email_verified_at' => now(),
               'streak' => 5,
               'total_points' => 100,
               'stars' => 10,
          ]);

          User::create([
               'name' => 'Mohamed Student',
               'email' => 'student3@chemtrack.com',
               'password' => Hash::make('password'),
               'is_admin' => false,
               'email_verified_at' => now(),
               'streak' => 7,
               'total_points' => 150,
               'stars' => 13,
          ]);
     }
}
