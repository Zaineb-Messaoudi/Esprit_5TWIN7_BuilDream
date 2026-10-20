<?php

use App\Enums\ExtensionStatus;
use App\Enums\RentalStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\RentalContract;
use App\Models\RentalExtension;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Helpers (their names start with "rentalWorkflow" so they never clash with
// the helpers of the other test files: all test files share one PHP process)
// ---------------------------------------------------------------------------

/** An administrator account. */
function rentalWorkflowAdmin(): User
{
    return User::factory()->create([
        'role' => UserRole::ADMIN,
        'role_setup_completed' => true,
    ]);
}

/** A published, available equipment listing with its own category. */
function rentalWorkflowEquipment(): Equipment
{
    return Equipment::factory()->create([
        'category_id' => Category::factory()->create(['status' => 'active'])->id,
        'approval_status' => 'published',
        'status' => 'available',
    ]);
}

// ---------------------------------------------------------------------------
// Back office = supervision: list, details, delete, approve / reject, PDF
// ---------------------------------------------------------------------------

test('only administrators can access rental back office pages', function () {
    $buyer = User::factory()->create([
        'role' => UserRole::BUYER,
        'role_setup_completed' => true,
    ]);

    $this->actingAs($buyer)
        ->get(route('admin.rentals.index'))
        ->assertForbidden();
});

test('the back office cannot create or edit rentals, contracts and extensions by hand', function () {
    // Records are created by users in the front office. The admin only supervises them,
    // so the create / store / edit / update routes must not exist any more.
    foreach (['rentals', 'rental-contracts', 'rental-extensions'] as $resource) {
        foreach (['create', 'store', 'edit', 'update'] as $action) {
            expect(Route::has("admin.{$resource}.{$action}"))
                ->toBeFalse("admin.{$resource}.{$action} should not exist");
        }

        foreach (['index', 'show', 'destroy'] as $action) {
            expect(Route::has("admin.{$resource}.{$action}"))
                ->toBeTrue("admin.{$resource}.{$action} should exist");
        }
    }

    // Sending a POST to the list address is refused (405 = method not allowed)
    $this->actingAs(rentalWorkflowAdmin())
        ->post('/admin/rentals', [])
        ->assertStatus(405);
});

test('admin can list search view and delete a rental linked to equipment', function () {
    $admin = rentalWorkflowAdmin();
    $equipment = rentalWorkflowEquipment();
    $rental = Rental::factory()->active()->create(['equipment_id' => $equipment->id]);

    // The list can be searched by the name of the equipment
    $this->actingAs($admin)
        ->get(route('admin.rentals.index', ['search' => $equipment->name]))
        ->assertOk()
        ->assertSee($rental->reference)
        ->assertSee($equipment->name);

    // The details page opens
    $this->actingAs($admin)
        ->get(route('admin.rentals.show', $rental))
        ->assertOk()
        ->assertSee($rental->reference);

    // Delete
    $this->actingAs($admin)
        ->delete(route('admin.rentals.destroy', $rental))
        ->assertRedirect(route('admin.rentals.index'));

    $this->assertDatabaseMissing('rentals', ['id' => $rental->id]);
});

test('admin can view delete and export a rental contract as pdf', function () {
    $admin = rentalWorkflowAdmin();
    $rental = Rental::factory()->pending()->create([
        'equipment_id' => rentalWorkflowEquipment()->id,
    ]);
    $contract = RentalContract::factory()->create(['rental_id' => $rental->id]);

    $this->actingAs($admin)
        ->get(route('admin.rental-contracts.show', $contract))
        ->assertOk()
        ->assertSee($contract->contract_number);

    $this->actingAs($admin)
        ->get(route('admin.rental-contracts.export-pdf', $contract))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    $this->actingAs($admin)
        ->delete(route('admin.rental-contracts.destroy', $contract))
        ->assertRedirect(route('admin.rental-contracts.index'));

    $this->assertDatabaseMissing('rental_contracts', ['id' => $contract->id]);
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

test('an approved extension cannot be deleted', function () {
    $admin = rentalWorkflowAdmin();
    $rental = Rental::factory()->active()->create([
        'equipment_id' => rentalWorkflowEquipment()->id,
    ]);
    $extension = RentalExtension::factory()->approved()->create(['rental_id' => $rental->id]);

    // An approved request already changed the rental: we keep it as history
    $this->actingAs($admin)
        ->delete(route('admin.rental-extensions.destroy', $extension))
        ->assertRedirect(route('admin.rental-extensions.index'))
        ->assertSessionHasErrors('extension');

    $this->assertDatabaseHas('rental_extensions', ['id' => $extension->id]);
});