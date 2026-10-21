<?php

use App\Enums\RentalStatus;
use App\Enums\UserRole;
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

test('live booking reads persisted equipment and server checks existing rental availability', function () {
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
        ->assertSee('Choose your rental dates')
        ->assertSee('AI Dashboard')
        ->assertSee('SolarShare AI Assistant')
        ->assertSee('equipment reservation')
        ->assertSee('Availability is checked again when you submit the request.')
        ->assertDontSee('Backend-connected preview.');
});

test('returning damaged equipment creates maintenance and makes it available after completion', function () {
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
        'status' => 'available',
        'approval_status' => 'published',
    ]);
    $rental = Rental::factory()->active()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $buyer->id,
    ]);

    $this->actingAs($buyer)
        ->post(route('rental.return.process', $rental), [
            'condition_before' => 'Good',
            'condition_after' => 'Cracked casing',
            'damage_detected' => '1',
            'comments' => 'Visible damage after return.',
        ])
        ->assertRedirect(route('front.buyer-rental-detail', $rental->fresh()->reference));

    $this->assertDatabaseHas('inspections', [
        'rental_id' => $rental->id,
        'damage_detected' => true,
    ]);
    $this->assertDatabaseHas('maintenances', [
        'equipment_id' => $equipment->id,
        'rental_id' => $rental->id,
        'status' => 'in_progress',
    ]);
    expect($equipment->fresh()->status)->toBe('maintenance');

    $maintenance = \App\Models\Maintenance::where('rental_id', $rental->id)->firstOrFail();
    $maintenance->update(['status' => 'completed']);

    expect($equipment->fresh()->status)->toBe('available');
    $this->assertDatabaseHas('maintenance_reports', [
        'maintenance_id' => $maintenance->id,
    ]);
});
