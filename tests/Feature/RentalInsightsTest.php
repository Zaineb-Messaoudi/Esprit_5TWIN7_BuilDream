<?php

use App\Enums\RentalStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\User;
use App\Services\RentalInsightsService;

// ---------------------------------------------------------------------------
// Helpers (names start with "insights" to avoid clashes between test files)
// ---------------------------------------------------------------------------

function insightsAdmin(): User
{
    return User::factory()->create([
        'role' => UserRole::ADMIN,
        'role_setup_completed' => true,
    ]);
}

/**
 * A rental 8 days long with a total of 50 (so the daily rate is 10):
 * it started 8 days ago and ended 3 days ago.
 */
function insightsLateRental(RentalStatus $status = RentalStatus::ACTIVE): Rental
{
    $equipment = Equipment::factory()->create([
        'category_id' => Category::factory()->create(['status' => 'active'])->id,
        'approval_status' => 'published',
        'status' => 'available',
    ]);

    return Rental::factory()->create([
        'equipment_id' => $equipment->id,
        'status' => $status->value,
        'start_date' => today()->subDays(8)->toDateString(),
        'end_date' => today()->subDays(3)->toDateString(),
        'total_amount' => 50,
    ]);
}

// ---------------------------------------------------------------------------
// Overdue rentals and late fees
// ---------------------------------------------------------------------------

test('an overdue rental has late days and a late fee', function () {
    $rental = insightsLateRental();
    $service = app(RentalInsightsService::class);

    // Duration 5 days (8 days ago -> 3 days ago), total 50: daily rate 10, 3 days late
    expect($service->lateDays($rental))->toBe(3)
        ->and($service->dailyRate($rental))->toBe(10.0)
        ->and($service->lateFee($rental))->toBe(30.0);
});

test('a completed rental is never overdue', function () {
    $rental = insightsLateRental(RentalStatus::COMPLETED);
    $service = app(RentalInsightsService::class);

    expect($service->lateDays($rental))->toBe(0)
        ->and($service->lateFee($rental))->toBe(0.0)
        ->and($service->overdueRentals())->toHaveCount(0);
});

test('the overdue page lists the late rental with its fee', function () {
    $rental = insightsLateRental();

    $this->actingAs(insightsAdmin())
        ->get(route('admin.rental-insights.overdue'))
        ->assertOk()
        ->assertSee($rental->reference)
        ->assertSee('30.00');
});

// ---------------------------------------------------------------------------
// Analytics
// ---------------------------------------------------------------------------

test('the analytics dashboard opens and shows its sections', function () {
    insightsLateRental();

    $this->actingAs(insightsAdmin())
        ->get(route('admin.rental-insights.dashboard'))
        ->assertOk()
        ->assertSee('Rental Analytics')
        ->assertSee('Rentals by status')
        ->assertSee('Most rented equipment');
});

test('only administrators can open the insights pages', function () {
    $buyer = User::factory()->create([
        'role' => UserRole::BUYER,
        'role_setup_completed' => true,
    ]);

    $this->actingAs($buyer)->get(route('admin.rental-insights.dashboard'))->assertForbidden();
    $this->actingAs($buyer)->get(route('admin.rental-insights.overdue'))->assertForbidden();
});