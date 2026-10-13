<?php

use App\Enums\RentalStatus;
use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\User;

test('a rental is linked to a real catalogue equipment listing', function () {
    $owner = User::factory()->create([
        'role' => UserRole::OWNER,
        'role_setup_completed' => true,
    ]);
    $renter = User::factory()->create([
        'role' => UserRole::BUYER,
        'role_setup_completed' => true,
    ]);
    $equipment = Equipment::factory()->create([
        'owner_id' => $owner->id,
        'approval_status' => 'published',
        'status' => 'available',
    ]);

    $rental = Rental::factory()->active()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $renter->id,
        'status' => RentalStatus::ACTIVE,
    ]);

    expect($rental->equipment->is($equipment))->toBeTrue()
        ->and($equipment->fresh()->rentals->contains($rental))->toBeTrue()
        ->and($rental->equipment_label)->toBe($equipment->name);
});

test('admins can find rentals by catalogue equipment name', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
        'role_setup_completed' => true,
    ]);
    $equipment = Equipment::factory()->create(['name' => 'SolarShare Search Battery']);
    $rental = Rental::factory()->create(['equipment_id' => $equipment->id]);

    $this->actingAs($admin)
        ->get(route('admin.rentals.index', ['search' => 'SolarShare Search Battery']))
        ->assertOk()
        ->assertSee($rental->reference)
        ->assertSee($equipment->name);
});
