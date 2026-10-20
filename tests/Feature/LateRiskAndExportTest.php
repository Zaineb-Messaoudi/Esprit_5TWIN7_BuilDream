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
use App\Services\LateRiskService;
use App\Services\RentalTimelineService;

// ---------------------------------------------------------------------------
// Helpers (names start with "riskExport" to avoid clashes between test files)
// ---------------------------------------------------------------------------

function riskExportAdmin(): User
{
    return User::factory()->create(['role' => UserRole::ADMIN, 'role_setup_completed' => true]);
}

function riskExportEquipment(): Equipment
{
    return Equipment::factory()->create([
        'category_id' => Category::factory()->create(['status' => 'active'])->id,
        'approval_status' => 'published',
        'status' => 'available',
    ]);
}

/**
 * A rental of $renter. By default it is ACTIVE and ends in 3 days (not late).
 * Pass $overrides to change any value, for example a past end date to make it late.
 */
function riskExportRental(?User $renter = null, array $overrides = []): Rental
{
    $renter ??= User::factory()->create(['role' => UserRole::BUYER, 'role_setup_completed' => true]);

    return Rental::factory()->create($overrides + [
        'user_id' => $renter->id,
        'equipment_id' => riskExportEquipment()->id,
        'status' => RentalStatus::ACTIVE->value,
        'start_date' => today()->subDays(2)->toDateString(),
        'end_date' => today()->addDays(3)->toDateString(),
        'total_amount' => 50,
    ]);
}

/** A rental that ended 3 days ago and is still ACTIVE: it is late. */
function riskExportLateRental(?User $renter = null): Rental
{
    return riskExportRental($renter, [
        'start_date' => today()->subDays(8)->toDateString(),
        'end_date' => today()->subDays(3)->toDateString(),
    ]);
}

// ---------------------------------------------------------------------------
// Delay risk score
// ---------------------------------------------------------------------------

test('a renter with no history has a low delay risk', function () {
    $risk = app(LateRiskService::class)->assess(riskExportRental());

    expect($risk['level'])->toBe('low')
        ->and($risk['score'])->toBe(LateRiskService::BASE_SCORE);
});

test('a rental that is already late has a high delay risk', function () {
    $risk = app(LateRiskService::class)->assess(riskExportLateRental());

    // base 10 + 50 + 5 x 3 late days = 75
    expect($risk['level'])->toBe('high')
        ->and($risk['score'])->toBe(75);
});

test('completed rentals lower the delay risk', function () {
    $renter = User::factory()->create(['role' => UserRole::BUYER, 'role_setup_completed' => true]);
    $rental = riskExportRental($renter);

    foreach (range(1, 3) as $i) {
        riskExportRental($renter, [
            'status' => RentalStatus::COMPLETED->value,
            'start_date' => today()->subDays(30)->toDateString(),
            'end_date' => today()->subDays(25)->toDateString(),
        ]);
    }

    // 10 - 15 = -5, kept at the minimum of 0
    expect(app(LateRiskService::class)->assess($rental)['score'])->toBe(0);
});

test('extensions already granted raise the delay risk', function () {
    $rental = riskExportRental();

    foreach (range(1, 2) as $i) {
        RentalExtension::factory()->approved()->create(['rental_id' => $rental->id]);
    }

    // 10 + 8 x 2 = 26
    expect(app(LateRiskService::class)->assess($rental)['score'])->toBe(26);
});

test('the overdue page shows the delay risk of each late rental', function () {
    riskExportLateRental();

    $this->actingAs(riskExportAdmin())
        ->get(route('admin.rental-insights.overdue'))
        ->assertOk()
        ->assertSee('Delay risk')
        ->assertSee('High (75)');
});

// ---------------------------------------------------------------------------
// Timeline
// ---------------------------------------------------------------------------

test('the timeline lists the contract signature and the extension of a rental', function () {
    $rental = riskExportRental();
    RentalContract::factory()->create([
        'rental_id' => $rental->id,
        'contract_status' => ContractStatus::SIGNED->value,
        'signed_at' => now()->subDay(),
    ]);
    RentalExtension::factory()->approved()->create(['rental_id' => $rental->id]);

    $titles = app(RentalTimelineService::class)->events($rental->fresh())->pluck('title')->implode(' | ');

    expect($titles)->toContain('Contract signed by the renter')
        ->and($titles)->toContain('Extension approved');
});

test('the timeline is sorted from the oldest event to the newest', function () {
    $rental = riskExportRental();
    RentalExtension::factory()->create(['rental_id' => $rental->id]);

    $dates = app(RentalTimelineService::class)->events($rental->fresh())->pluck('at')->map->timestamp->all();

    $sorted = $dates;
    sort($sorted);

    expect($dates)->toBe($sorted);
});

test('the rental page shows the delay risk and the timeline', function () {
    $rental = riskExportRental();

    $this->actingAs(riskExportAdmin())
        ->get(route('admin.rentals.show', $rental))
        ->assertOk()
        ->assertSee('Delay risk')
        ->assertSee('Timeline')
        ->assertSee("Rental {$rental->reference} created");
});

// ---------------------------------------------------------------------------
// CSV export
// ---------------------------------------------------------------------------

test('the admin can export the rentals as a CSV file', function () {
    $rental = riskExportRental();

    $response = $this->actingAs(riskExportAdmin())
        ->get(route('admin.rental-insights.export'))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $csv = $response->streamedContent();

    expect($csv)->toContain('Reference')          // header line
        ->and($csv)->toContain($rental->reference); // one line per rental
});

test('the CSV uses semicolons and decimal commas, as Excel expects on a French computer', function () {
    riskExportRental(null, ['total_amount' => 50]);

    $csv = $this->actingAs(riskExportAdmin())
        ->get(route('admin.rental-insights.export'))
        ->streamedContent();

    $header = strtok(ltrim($csv, "\xEF\xBB\xBF"), "\n"); // first line, without the BOM

    expect($header)->toContain('Reference;Renter;')
        ->and($csv)->toContain('50,00');
});

test('the international CSV format uses commas and decimal points', function () {
    riskExportRental(null, ['total_amount' => 50]);

    $csv = $this->actingAs(riskExportAdmin())
        ->get(route('admin.rental-insights.export', ['delimiter' => 'comma']))
        ->streamedContent();

    $header = strtok(ltrim($csv, "\xEF\xBB\xBF"), "\n");

    expect($header)->toContain('Reference,Renter,')
        ->and($csv)->toContain('50.00');
});

test('the CSV export follows the status filter', function () {
    $active = riskExportRental();
    $completed = riskExportRental(null, ['status' => RentalStatus::COMPLETED->value]);

    $csv = $this->actingAs(riskExportAdmin())
        ->get(route('admin.rental-insights.export', ['status' => 'completed']))
        ->streamedContent();

    expect($csv)->toContain($completed->reference)
        ->and($csv)->not->toContain($active->reference);
});

test('a name that looks like a spreadsheet formula is made harmless in the CSV', function () {
    $renter = User::factory()->create([
        'name' => '=HYPERLINK("http://example.com")',
        'role' => UserRole::BUYER,
        'role_setup_completed' => true,
    ]);
    riskExportRental($renter);

    $csv = $this->actingAs(riskExportAdmin())
        ->get(route('admin.rental-insights.export'))
        ->streamedContent();

    // The apostrophe in front stops Excel from running it as a formula
    expect($csv)->toContain("'=HYPERLINK");
});

test('only administrators can export the rentals', function () {
    $buyer = User::factory()->create(['role' => UserRole::BUYER, 'role_setup_completed' => true]);

    $this->actingAs($buyer)->get(route('admin.rental-insights.export'))->assertForbidden();
});