<?php

use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\User;

it('requires authentication for marketplace booking endpoints', function () {
    $this->postJson(route('reservations.store'), [])
        ->assertUnauthorized();
});

it('requires authentication for the account endpoint', function () {
    $this->getJson(route('api.me'))
        ->assertUnauthorized();
});

it('returns published available catalogue equipment through the API', function () {
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
        'name' => 'API Solar Battery',
        'approval_status' => 'published',
        'status' => 'available',
    ]);

    $this->actingAs($buyer, 'sanctum')
        ->getJson(route('catalog.index'))
        ->assertOk()
        ->assertJsonFragment(['name' => 'API Solar Battery']);

    expect($equipment->fresh()->shouldBeSearchable())->toBeTrue();
});

it('returns catalogue details and date availability through the API', function () {
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
        'name' => 'API Detail Battery',
        'approval_status' => 'published',
        'status' => 'available',
    ]);

    $this->actingAs($buyer, 'sanctum')
        ->getJson(route('catalog.show', $equipment))
        ->assertOk()
        ->assertJsonPath('name', 'API Detail Battery')
        ->assertJsonPath('approval_status', 'published');

    $this->actingAs($buyer, 'sanctum')
        ->getJson(route('catalog.availability', $equipment, [
            'start' => now()->addDay()->toDateString(),
            'end' => now()->addDays(3)->toDateString(),
        ]))
        ->assertOk()
        ->assertJsonStructure(['equipment_id', 'availability', 'reserved_periods', 'rental_periods']);
});
