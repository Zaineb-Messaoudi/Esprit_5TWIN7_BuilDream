<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Persona 1: Super Admin
        User::factory()->create([
            'name' => 'Solar Admin',
            'email' => 'admin@solarshare.com',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN,
            'email_verified_at' => now(),
        ]);

        // Persona 2: Buyer
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'user@solarshare.com',
            'password' => bcrypt('password'),
            'role' => UserRole::BUYER,
            'email_verified_at' => now(),
        ]);

        // Persona 3: Equipment Owner
        User::factory()->create([
            'name' => 'Sami Owner',
            'email' => 'owner@solarshare.com',
            'password' => bcrypt('password'),
            'role' => UserRole::OWNER,
            'email_verified_at' => now(),
        ]);

        // Generic users for testing
        User::factory(10)->create();
    }
}
