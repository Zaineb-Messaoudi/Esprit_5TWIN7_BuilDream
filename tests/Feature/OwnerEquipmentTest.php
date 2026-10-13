<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;

function ownerWorkspaceUser(): User
{
    return User::factory()->create([
        'role' => UserRole::OWNER,
        'role_setup_completed' => true,
    ]);
}

test('owner can publish equipment from the front office', function () {
    $owner = ownerWorkspaceUser();
    $category = Category::factory()->create(['status' => 'active']);

    $this->actingAs($owner)
        ->post(route('front.my-equipment.store'), [
            'category_id' => $category->id,
            'name' => 'Owner battery',
            'brand' => 'Voltix',
            'model' => 'PB-1000',
            'price_per_day' => 18,
            'condition' => 'good',
            'location' => 'Tunis',
            'status' => 'available',
            'energy' => ['power_watts' => 500, 'capacity_wh' => 1000, 'technology' => 'Lithium-ion'],
        ])
        ->assertRedirect(route('front.my-equipment'));

    $equipment = Equipment::where('name', 'Owner battery')->firstOrFail();
    expect($equipment->owner_id)->toBe($owner->id)
        ->and($equipment->energyProfile->capacity_wh)->toBe(1000);
});

test('owner can view and update their own equipment', function () {
    $owner = ownerWorkspaceUser();
    $equipment = Equipment::factory()->create(['owner_id' => $owner->id]);

    $this->actingAs($owner)
        ->get(route('front.my-equipment'))
        ->assertOk()
        ->assertSee($equipment->name);

    $this->get(route('front.my-equipment-edit', $equipment))->assertOk()->assertSee('Edit equipment');

    $this->put(route('front.my-equipment.update', $equipment), [
        'category_id' => $equipment->category_id,
        'name' => 'Updated owner equipment',
        'price_per_day' => 25,
        'condition' => 'new',
        'location' => 'Sousse',
        'status' => 'available',
    ])->assertRedirect(route('front.my-equipment'));

    expect($equipment->refresh()->name)->toBe('Updated owner equipment');
});

test('owner cannot edit another owners equipment', function () {
    $owner = ownerWorkspaceUser();
    $otherOwner = ownerWorkspaceUser();
    $equipment = Equipment::factory()->create(['owner_id' => $otherOwner->id]);

    $this->actingAs($owner)
        ->get(route('front.my-equipment-edit', $equipment))
        ->assertForbidden();
});

test('buyer cannot open the owner equipment workspace', function () {
    $buyer = User::factory()->create(['role' => UserRole::BUYER, 'role_setup_completed' => true]);

    $this->actingAs($buyer)
        ->get(route('front.my-equipment'))
        ->assertForbidden();
});
