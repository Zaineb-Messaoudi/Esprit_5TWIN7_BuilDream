<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $equipments = Equipment::all();
        $users = User::all();

        // Equipment belongs to Student 1. This module reuses existing equipment
        // instead of creating duplicate or incomplete equipment records.
        if ($equipments->isEmpty()) {
            return;
        }
        if ($users->isEmpty()) {
            $users = User::factory(5)->create();
        }

        for ($i = 0; $i < 10; $i++) {
            $reservation = Reservation::factory()->create([
                'equipment_id' => $equipments->random()->id,
                'user_id' => $users->random()->id,
            ]);

            Payment::factory(rand(1, 2))->create([
                'reservation_id' => $reservation->id,
            ]);

            Invoice::factory()->create([
                'reservation_id' => $reservation->id,
                'subtotal' => $reservation->total_amount,
                'tax' => round($reservation->total_amount * 0.19, 2),
                'total' => round($reservation->total_amount * 1.19, 2),
            ]);
        }
    }
}
