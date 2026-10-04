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
            'password' => 'password',
            'phone_number' => '+216 70 000 001',
            'address' => 'Tunis, Tunisia',
            'role' => UserRole::ADMIN,
            'role_setup_completed' => true,
            'email_verified_at' => now(),
        ]);

        // Persona 2: Buyer
        User::factory()->create([
            'name' => 'Leila Buyer',
            'email' => 'user@solarshare.com',
            'password' => 'password',
            'phone_number' => '+216 70 000 002',
            'address' => 'Sousse, Tunisia',
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
            'email_verified_at' => now(),
        ]);

        // Persona 3: Equipment Owner
        User::factory()->create([
            'name' => 'Sami Owner',
            'email' => 'owner@solarshare.com',
            'password' => 'password',
            'phone_number' => '+216 70 000 003',
            'address' => 'Sfax, Tunisia',
            'role' => UserRole::OWNER,
            'role_setup_completed' => true,
            'email_verified_at' => now(),
        ]);

        // Persona 4: New account that still needs to select a buyer or owner role.
        User::factory()->create([
            'name' => 'New SolarShare Member',
            'email' => 'new-member@solarshare.com',
            'password' => 'password',
            'role' => UserRole::USER,
            'role_setup_completed' => false,
            'email_verified_at' => now(),
        ]);

        // Additional buyer accounts; elevated roles are assigned explicitly above.
        User::factory(10)->create();
    }
}
