<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user'
        ]);

        // Create admin account
        User::factory()->create([
            'full_name' => 'Admin',
            'email' => 'admin@pisay.com',
            'password' => bcrypt('admin123'), // Set a specific password
            'role' => 'admin'  // Assuming you have a role column in your users table
        ]);
    }
}
