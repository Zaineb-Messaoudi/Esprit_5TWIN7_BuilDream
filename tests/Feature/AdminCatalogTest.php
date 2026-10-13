<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;

function catalogAdmin(): User
{
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
        'role_setup_completed' => true,
    ]);

    return $admin;
}

function catalogOwner(): User
{
    return User::factory()->create([
        'role' => UserRole::OWNER,
        'role_setup_completed' => true,
    ]);
}

test('admin can create a category', function () {
    $this->actingAs(catalogAdmin())
        ->post(route('admin.categories.store'), [
            'name' => 'Portable solar panels',
            'description' => 'Foldable solar equipment.',
            'status' => 'active',
        ])
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', ['name' => 'Portable solar panels', 'status' => 'active']);
});

test('admin can update and delete an unused category', function () {
    $admin = catalogAdmin();
    $category = Category::factory()->create();

    $this->actingAs($admin)
        ->put(route('admin.categories.update', $category), [
            'name' => 'Updated category',
            'description' => 'Updated description.',
            'status' => 'inactive',
        ])
        ->assertRedirect(route('admin.categories.index'));

    expect($category->refresh()->name)->toBe('Updated category')
        ->and($category->status)->toBe('inactive');

    $this->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('catalog admin pages render for administrators', function () {
    $admin = catalogAdmin();
    Category::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.categories.index'))
        ->assertOk()
        ->assertSee('Categories');

    $this->get(route('admin.equipment.index'))
        ->assertOk()
        ->assertSee('Equipment');
});

test('admin can create equipment with its energy profile', function () {
    $category = Category::factory()->create();
    $owner = catalogOwner();

    $this->actingAs(catalogAdmin())
        ->post(route('admin.equipment.store'), [
            'category_id' => $category->id,
            'owner_id' => $owner->id,
            'name' => 'Portable battery 1000 Wh',
            'description' => 'A portable battery.',
            'brand' => 'Voltix',
            'model' => 'PB-1000',
            'price_per_day' => 18,
            'condition' => 'good',
            'location' => 'Tunis',
            'status' => 'available',
            'energy' => [
                'power_watts' => 500,
                'voltage' => 24,
                'capacity_wh' => 1000,
                'efficiency' => 92,
                'technology' => 'Lithium-ion',
                'max_output' => 1000,
                'operating_duration' => 8,
            ],
        ])
        ->assertRedirect(route('admin.equipment.index'));

    $equipment = Equipment::with('energyProfile')->firstOrFail();
    expect($equipment->owner_id)->toBe($owner->id)
        ->and($equipment->energyProfile->power_watts)->toBe(500)
        ->and((int) $equipment->energyProfile->capacity_wh)->toBe(1000);
});

test('admin can view update and soft delete equipment', function () {
    $admin = catalogAdmin();
    $equipment = Equipment::factory()->create(['owner_id' => catalogOwner()->id]);

    $this->actingAs($admin)
        ->get(route('admin.equipment.show', $equipment))
        ->assertOk()
        ->assertSee($equipment->name);

    $this->put(route('admin.equipment.update', $equipment), [
        'category_id' => $equipment->category_id,
        'owner_id' => $equipment->owner_id,
        'name' => 'Updated equipment',
        'description' => 'Updated equipment description.',
        'price_per_day' => 42,
        'condition' => 'new',
        'location' => 'Sousse',
        'status' => 'available',
        'approval_status' => 'published',
    ])->assertRedirect(route('admin.equipment.index'));

    expect($equipment->refresh()->name)->toBe('Updated equipment')
        ->and((float) $equipment->price_per_day)->toBe(42.0);

    $this->delete(route('admin.equipment.destroy', $equipment))
        ->assertRedirect(route('admin.equipment.index'));

    $this->assertSoftDeleted('equipment', ['id' => $equipment->id]);
});

test('equipment validation rejects incomplete catalogue data', function () {
    $category = Category::factory()->create();
    $owner = catalogOwner();

    $this->actingAs(catalogAdmin())
        ->from(route('admin.equipment.create'))
        ->post(route('admin.equipment.store'), [
            'category_id' => $category->id,
            'owner_id' => $owner->id,
            'name' => '',
            'price_per_day' => -1,
            'condition' => 'invalid',
            'location' => '',
            'status' => 'available',
        ])
        ->assertSessionHasErrors(['name', 'price_per_day', 'condition', 'location']);
});

test('non owner users cannot be assigned equipment', function () {
    $category = Category::factory()->create();
    $buyer = User::factory()->create(['role' => UserRole::BUYER]);

    $this->actingAs(catalogAdmin())
        ->from(route('admin.equipment.create'))
        ->post(route('admin.equipment.store'), [
            'category_id' => $category->id,
            'owner_id' => $buyer->id,
            'name' => 'Invalid listing',
            'price_per_day' => 10,
            'condition' => 'good',
            'location' => 'Tunis',
            'status' => 'available',
        ])
        ->assertSessionHasErrors('owner_id');
});

test('category with equipment cannot be deleted', function () {
    $category = Category::factory()->create();
    Equipment::factory()->create(['category_id' => $category->id, 'owner_id' => catalogOwner()->id]);

    $this->actingAs(catalogAdmin())
        ->delete(route('admin.categories.destroy', $category))
        ->assertSessionHasErrors('category');

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('admin can view a category with its related equipment', function () {
    $category = Category::factory()->create(['name' => 'Solar panels']);
    $equipment = Equipment::factory()->create([
        'category_id' => $category->id,
        'owner_id' => catalogOwner()->id,
    ]);

    $this->actingAs(catalogAdmin())
        ->get(route('admin.categories.show', $category))
        ->assertOk()
        ->assertSee($category->name)
        ->assertSee($equipment->name);
});

test('admin can create update view and delete an energy profile', function () {
    $equipment = Equipment::factory()->create(['owner_id' => catalogOwner()->id]);
    $admin = catalogAdmin();

    $this->actingAs($admin)
        ->post(route('admin.equipment.energy-profile.store', $equipment), [
            'power_watts' => 500,
            'voltage' => 24,
            'capacity_wh' => 1000,
            'efficiency' => 92,
            'technology' => 'Lithium-ion',
            'max_output' => 1000,
            'operating_duration' => 8,
        ])
        ->assertRedirect(route('admin.equipment.energy-profile.index', $equipment));

    $profile = $equipment->energyProfile()->firstOrFail();

    $this->get(route('admin.equipment.energy-profile.show', $equipment))
        ->assertOk()
        ->assertSee('Lithium-ion');

    $this->put(route('admin.equipment.energy-profile.update', $equipment), [
        'power_watts' => 600,
        'technology' => 'Updated technology',
    ])->assertRedirect(route('admin.equipment.energy-profile.index', $equipment));

    expect($profile->refresh()->power_watts)->toBe(600);

    $this->delete(route('admin.equipment.energy-profile.destroy', $equipment))
        ->assertRedirect(route('admin.equipment.energy-profile.index', $equipment));

    $this->assertDatabaseMissing('energy_profiles', ['id' => $profile->id]);
});

test('equipment factory can create its energy profile relationship', function () {
    $equipment = Equipment::factory()->withEnergyProfile()->create();

    expect($equipment->energyProfile)->not->toBeNull()
        ->and($equipment->energyProfile->equipment_id)->toBe($equipment->id);
});
