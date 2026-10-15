<?php

use App\Enums\ContractStatus;
use App\Enums\ExtensionStatus;
use App\Enums\RentalStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\RentalContract;
use App\Models\RentalExtension;
use App\Models\User;

function rentalWorkflowAdmin(): User
{
    return User::factory()->create([
        'role' => UserRole::ADMIN,
        'role_setup_completed' => true,
    ]);
}

function rentalWorkflowEquipment(): Equipment
{
    return Equipment::factory()->create([
        'category_id' => Category::factory()->create(['status' => 'active'])->id,
        'approval_status' => 'published',
        'status' => 'available',
    ]);
}

test('only administrators can access rental back office pages', function () {
    $buyer = User::factory()->create([
        'role' => UserRole::BUYER,
        'role_setup_completed' => true,
    ]);

    $this->actingAs($buyer)
        ->get(route('admin.rentals.index'))
        ->assertForbidden();
});

test('admin can create update search and delete a rental linked to equipment', function () {
    $admin = rentalWorkflowAdmin();
    $renter = User::factory()->create([
        'role' => UserRole::BUYER,
        'role_setup_completed' => true,
    ]);
    $equipment = rentalWorkflowEquipment();

    $this->actingAs($admin)
        ->post(route('admin.rentals.store'), [
            'user_id' => $renter->id,
            'equipment_id' => $equipment->id,
            'start_date' => today()->toDateString(),
            'end_date' => today()->addDays(3)->toDateString(),
            'total_amount' => 75,
            'status' => RentalStatus::ACTIVE->value,
        ])
        ->assertRedirect(route('admin.rentals.index'));

    $rental = Rental::query()->latest('id')->firstOrFail();
    expect($rental->reference)->toStartWith('RNT-')
        ->and($rental->equipment->is($equipment))->toBeTrue();

    $this->actingAs($admin)
        ->get(route('admin.rentals.index', ['search' => $equipment->name]))
        ->assertOk()
        ->assertSee($rental->reference)
        ->assertSee($equipment->name);

    $this->actingAs($admin)
        ->put(route('admin.rentals.update', $rental), [
            'user_id' => $renter->id,
            'equipment_id' => $equipment->id,
            'start_date' => today()->addDay()->toDateString(),
            'end_date' => today()->addDays(5)->toDateString(),
            'total_amount' => 125,
            'status' => RentalStatus::PENDING->value,
        ])
        ->assertRedirect(route('admin.rentals.index'));

    expect($rental->refresh()->total_amount)->toEqual(125)
        ->and($rental->status)->toBe(RentalStatus::PENDING);

    $this->actingAs($admin)
        ->delete(route('admin.rentals.destroy', $rental))
        ->assertRedirect(route('admin.rentals.index'));

    $this->assertDatabaseMissing('rentals', ['id' => $rental->id]);
});

test('rental validation rejects an end date before the start date', function () {
    $admin = rentalWorkflowAdmin();
    $renter = User::factory()->create(['role' => UserRole::BUYER]);
    $equipment = rentalWorkflowEquipment();

    $this->actingAs($admin)
        ->from(route('admin.rentals.create'))
        ->post(route('admin.rentals.store'), [
            'user_id' => $renter->id,
            'equipment_id' => $equipment->id,
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-19',
            'total_amount' => 50,
            'status' => RentalStatus::PENDING->value,
        ])
        ->assertSessionHasErrors('end_date');
});

test('admin can create update and delete a rental contract', function () {
    $admin = rentalWorkflowAdmin();
    $rental = Rental::factory()->pending()->create([
        'equipment_id' => rentalWorkflowEquipment()->id,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.rental-contracts.store'), [
            'rental_id' => $rental->id,
            'terms' => 'Return the equipment in the same condition.',
            'deposit_amount' => 50,
            'contract_status' => ContractStatus::SIGNED->value,
        ])
        ->assertRedirect(route('admin.rental-contracts.index'));

    $contract = RentalContract::where('rental_id', $rental->id)->firstOrFail();
    expect($contract->contract_number)->toStartWith('CTR-')
        ->and($contract->signed_at)->not->toBeNull();

    $this->actingAs($admin)
        ->put(route('admin.rental-contracts.update', $contract), [
            'terms' => 'Updated rental terms.',
            'deposit_amount' => 0,
            'contract_status' => ContractStatus::DRAFT->value,
            'signed_at' => now()->toDateTimeString(),
        ])
        ->assertRedirect(route('admin.rental-contracts.index'));

    expect($contract->refresh()->contract_status)->toBe(ContractStatus::DRAFT)
        ->and($contract->signed_at)->toBeNull();

    $this->actingAs($admin)
        ->delete(route('admin.rental-contracts.destroy', $contract))
        ->assertRedirect(route('admin.rental-contracts.index'));

    $this->assertDatabaseMissing('rental_contracts', ['id' => $contract->id]);
});

test('a rental cannot receive two contracts', function () {
    $admin = rentalWorkflowAdmin();
    $rental = Rental::factory()->create([
        'equipment_id' => rentalWorkflowEquipment()->id,
    ]);
    RentalContract::factory()->create(['rental_id' => $rental->id]);

    $this->actingAs($admin)
        ->from(route('admin.rental-contracts.create'))
        ->post(route('admin.rental-contracts.store'), [
            'rental_id' => $rental->id,
            'terms' => 'Duplicate contract attempt.',
            'deposit_amount' => 0,
            'contract_status' => ContractStatus::DRAFT->value,
        ])
        ->assertSessionHasErrors('rental_id');
});

test('admin can create and update a pending extension with an automatic amount', function () {
    $admin = rentalWorkflowAdmin();
    $rental = Rental::factory()->active()->create([
        'equipment_id' => rentalWorkflowEquipment()->id,
        'start_date' => today()->subDays(4),
        'end_date' => today()->addDays(2),
        'total_amount' => 60,
    ]);

    $newEnd = today()->addDays(5);

    $this->actingAs($admin)
        ->post(route('admin.rental-extensions.store'), [
            'rental_id' => $rental->id,
            'new_end_date' => $newEnd->toDateString(),
            'reason' => 'Trip extended.',
        ])
        ->assertRedirect(route('admin.rental-extensions.index'));

    $extension = RentalExtension::where('rental_id', $rental->id)->firstOrFail();
    expect($extension->status)->toBe(ExtensionStatus::PENDING)
        ->and((float) $extension->additional_amount)->toBeGreaterThan(0);

    $updatedEnd = today()->addDays(6);

    $this->actingAs($admin)
        ->put(route('admin.rental-extensions.update', $extension), [
            'new_end_date' => $updatedEnd->toDateString(),
            'additional_amount' => 30,
            'reason' => 'Trip extended again.',
        ])
        ->assertRedirect(route('admin.rental-extensions.index'));

    expect($extension->refresh()->new_end_date->isSameDay($updatedEnd))->toBeTrue()
        ->and((float) $extension->additional_amount)->toBe(30.0);
});

test('approving an extension updates the rental and rejecting one does not', function () {
    $admin = rentalWorkflowAdmin();
    $approvedRental = Rental::factory()->active()->create([
        'equipment_id' => rentalWorkflowEquipment()->id,
        'total_amount' => 100,
    ]);
    $approved = RentalExtension::factory()->create([
        'rental_id' => $approvedRental->id,
        'new_end_date' => $approvedRental->end_date->copy()->addDays(2),
        'additional_amount' => 40,
    ]);
    $oldEnd = $approvedRental->end_date;

    $this->actingAs($admin)
        ->post(route('admin.rental-extensions.approve', $approved))
        ->assertRedirect()
        ->assertSessionHas('status', 'extension-approved');

    expect($approved->refresh()->status)->toBe(ExtensionStatus::APPROVED)
        ->and($approvedRental->refresh()->end_date->isAfter($oldEnd))->toBeTrue()
        ->and((float) $approvedRental->total_amount)->toBe(140.0);

    $rejectedRental = Rental::factory()->active()->create([
        'equipment_id' => rentalWorkflowEquipment()->id,
        'total_amount' => 100,
    ]);
    $rejected = RentalExtension::factory()->create([
        'rental_id' => $rejectedRental->id,
        'new_end_date' => $rejectedRental->end_date->copy()->addDays(2),
        'additional_amount' => 40,
    ]);
    $rejectedEnd = $rejectedRental->end_date;

    $this->actingAs($admin)
        ->post(route('admin.rental-extensions.reject', $rejected))
        ->assertRedirect()
        ->assertSessionHas('status', 'extension-rejected');

    expect($rejected->refresh()->status)->toBe(ExtensionStatus::REJECTED)
        ->and($rejectedRental->refresh()->end_date->isSameDay($rejectedEnd))->toBeTrue()
        ->and((float) $rejectedRental->total_amount)->toBe(100.0);
});

test('processed extensions cannot be edited or deleted', function () {
    $admin = rentalWorkflowAdmin();
    $rental = Rental::factory()->active()->create([
        'equipment_id' => rentalWorkflowEquipment()->id,
    ]);
    $extension = RentalExtension::factory()->approved()->create(['rental_id' => $rental->id]);

    $this->actingAs($admin)
        ->get(route('admin.rental-extensions.edit', $extension))
        ->assertRedirect(route('admin.rental-extensions.show', $extension))
        ->assertSessionHasErrors('extension');

    $this->actingAs($admin)
        ->delete(route('admin.rental-extensions.destroy', $extension))
        ->assertRedirect(route('admin.rental-extensions.index'))
        ->assertSessionHasErrors('extension');

    $this->assertDatabaseHas('rental_extensions', ['id' => $extension->id]);
});
