<?php

use App\Enums\RentalStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\User;

test('public equipment detail reads a persisted catalogue listing', function () {
    $owner = User::factory()->create([
        'role' => UserRole::OWNER,
        'role_setup_completed' => true,
    ]);
    $equipment = Equipment::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Persisted Solar Battery',
        'approval_status' => 'published',
        'status' => 'available',
    ]);

    $this->get(route('front.equipment.show', $equipment))
        ->assertOk()
        ->assertSee('Persisted Solar Battery')
        ->assertSee($equipment->brand)
        ->assertSee($equipment->location);
});

test('booking preview reads persisted equipment and existing rental availability', function () {
    $owner = User::factory()->create([
        'role' => UserRole::OWNER,
        'role_setup_completed' => true,
    ]);
    $buyer = User::factory()->create([
        'role' => UserRole::BUYER,
        'role_setup_completed' => true,
    ]);
    $equipment = Equipment::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Availability Connected Battery',
        'approval_status' => 'published',
        'status' => 'available',
    ]);

    Rental::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $buyer->id,
        'status' => RentalStatus::ACTIVE,
        'start_date' => '2026-10-09',
        'end_date' => '2026-10-11',
    ]);

    $this->actingAs($buyer)
        ->get(route('front.reserve', ['equipment' => $equipment->id]))
        ->assertOk()
        ->assertSee('Availability Connected Battery')
        ->assertSee('2026-10-09')
        ->assertSee('2026-10-11')
        ->assertSee('Backend-connected preview.');
});
