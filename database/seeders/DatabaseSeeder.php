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
        User::firstOrCreate(['email' => 'admin@solarshare.com'], [
            'name' => 'Solar Admin',
            'password' => 'password',
            'phone_number' => '+216 70 000 001',
            'address' => 'Tunis, Tunisia',
            'role' => UserRole::ADMIN,
            'role_setup_completed' => true,
            'email_verified_at' => now(),
        ]);

        // Persona 2: Buyer
        User::firstOrCreate(['email' => 'user@solarshare.com'], [
            'name' => 'Leila Buyer',
            'password' => 'password',
            'phone_number' => '+216 70 000 002',
            'address' => 'Sousse, Tunisia',
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
            'email_verified_at' => now(),
        ]);

        // Persona 3: Equipment Owner
        User::firstOrCreate(['email' => 'owner@solarshare.com'], [
            'name' => 'Sami Owner',
            'password' => 'password',
            'phone_number' => '+216 70 000 003',
            'address' => 'Sfax, Tunisia',
            'role' => UserRole::OWNER,
            'role_setup_completed' => true,
            'email_verified_at' => now(),
        ]);

        // Persona 4: New account that still needs to select a buyer or owner role.
        User::firstOrCreate(['email' => 'new-member@solarshare.com'], [
            'name' => 'New SolarShare Member',
            'password' => 'password',
            'role' => UserRole::USER,
            'role_setup_completed' => false,
            'email_verified_at' => now(),
        ]);


        // Stable additional buyer accounts avoid growing the database on each seed.
        foreach (range(1, 10) as $number) {
            $suffix = str_pad((string) $number, 2, '0', STR_PAD_LEFT);

            User::firstOrCreate(["email" => "buyer{$suffix}@solarshare.com"], [
                'name' => "SolarShare Buyer {$suffix}",
                'password' => 'password',
                'role' => UserRole::BUYER,
                'role_setup_completed' => true,
                'email_verified_at' => now(),
            ]);
        }
        // Generic users for testing
        User::factory(10)->create();
        $this->call(ReservationSeeder::class);

    }
}
