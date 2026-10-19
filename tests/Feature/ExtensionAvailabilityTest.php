<?php

use App\Enums\RentalStatus;
use App\Enums\UserRole;
use App\Events\ExtensionApproved;
use App\Events\ExtensionRequested;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\RentalExtension;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ExtensionAdvisorService;
use Illuminate\Support\Facades\Event;

// ---------------------------------------------------------------------------
// Helpers (names start with "availability" to avoid clashes between test files)
// ---------------------------------------------------------------------------

function availabilityUser(UserRole $role): User
{
    return User::factory()->create(['role' => $role, 'role_setup_completed' => true]);
}

/**
 * An owner with an equipment, a renter and an ACTIVE rental of 5 days:
 * it started 2 days ago and ends in 3 days. The "extra period" of an extension
 * therefore starts 4 days from today.
 */
function availabilityScenario(): array
{
    $owner = availabilityUser(UserRole::OWNER);
    $renter = availabilityUser(UserRole::BUYER);

    $equipment = Equipment::factory()->create([
        'owner_id' => $owner->id,
        'category_id' => Category::factory()->create(['status' => 'active'])->id,
        'approval_status' => 'published',
        'status' => 'available',
    ]);

    $rental = Rental::factory()->create([
        'user_id' => $renter->id,
        'equipment_id' => $equipment->id,
        'status' => RentalStatus::ACTIVE->value,
        'start_date' => today()->subDays(2)->toDateString(),
        'end_date' => today()->addDays(3)->toDateString(),
        'total_amount' => 90,
    ]);

    return compact('owner', 'renter', 'equipment', 'rental');
}

/** A reservation of $equipment from "today + $from days" to "today + $to days". */
function availabilityReservation(Equipment $equipment, int $from, int $to, string $status = 'confirmed'): Reservation
{
    return Reservation::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => availabilityUser(UserRole::BUYER)->id,
        'start_date' => today()->addDays($from)->toDateString(),
        'end_date' => today()->addDays($to)->toDateString(),
        'status' => $status,
    ]);
}

/** The renter asks to extend the rental until "today + $days days". */
function availabilityRequest($test, User $renter, Rental $rental, int $days)
{
    return $test->actingAs($renter)->post(
        route('rental.extensions.store', $rental),
        ['new_end_date' => today()->addDays($days)->toDateString()]
    );
}

// ---------------------------------------------------------------------------
// The renter requests an extension: edge cases of the overlap
// (the extra period is day +4 -> day +6, because the rental ends on day +3)
// ---------------------------------------------------------------------------

test('an extension is refused when a reservation overlaps the extra period', function () {
    Event::fake([ExtensionRequested::class]);
    ['renter' => $renter, 'equipment' => $equipment, 'rental' => $rental] = availabilityScenario();
    availabilityReservation($equipment, 5, 7); // inside the extra period

    availabilityRequest($this, $renter, $rental, 6)->assertSessionHasErrors('new_end_date');

    expect(RentalExtension::where('rental_id', $rental->id)->count())->toBe(0);
});

test('an extension is refused when a reservation starts on the requested end date', function () {
    Event::fake([ExtensionRequested::class]);
    ['renter' => $renter, 'equipment' => $equipment, 'rental' => $rental] = availabilityScenario();
    availabilityReservation($equipment, 6, 8); // starts exactly on the last requested day

    availabilityRequest($this, $renter, $rental, 6)->assertSessionHasErrors('new_end_date');
});

test('an extension is allowed when the next reservation starts the day after the new end date', function () {
    Event::fake([ExtensionRequested::class]);
    ['renter' => $renter, 'equipment' => $equipment, 'rental' => $rental] = availabilityScenario();
    availabilityReservation($equipment, 7, 9); // starts one day after the requested end date

    availabilityRequest($this, $renter, $rental, 6)->assertSessionHasNoErrors();

    expect(RentalExtension::where('rental_id', $rental->id)->count())->toBe(1);
});

test('a reservation that ends before the extra period does not block the extension', function () {
    Event::fake([ExtensionRequested::class]);
    ['renter' => $renter, 'equipment' => $equipment, 'rental' => $rental] = availabilityScenario();
    availabilityReservation($equipment, 1, 3); // before the extra period

    availabilityRequest($this, $renter, $rental, 6)->assertSessionHasNoErrors();
});

test('a cancelled reservation does not block the extension', function () {
    Event::fake([ExtensionRequested::class]);
    ['renter' => $renter, 'equipment' => $equipment, 'rental' => $rental] = availabilityScenario();
    availabilityReservation($equipment, 5, 7, 'cancelled');

    availabilityRequest($this, $renter, $rental, 6)->assertSessionHasNoErrors();
});

test('another rental of the same equipment blocks the extension', function () {
    Event::fake([ExtensionRequested::class]);
    ['renter' => $renter, 'equipment' => $equipment, 'rental' => $rental] = availabilityScenario();

    Rental::factory()->create([
        'user_id' => availabilityUser(UserRole::BUYER)->id,
        'equipment_id' => $equipment->id,
        'status' => RentalStatus::PENDING->value,
        'start_date' => today()->addDays(5)->toDateString(),
        'end_date' => today()->addDays(8)->toDateString(),
    ]);

    availabilityRequest($this, $renter, $rental, 6)->assertSessionHasErrors('new_end_date');
});

test('the refusal message tells how far the rental can be extended', function () {
    Event::fake([ExtensionRequested::class]);
    ['renter' => $renter, 'equipment' => $equipment, 'rental' => $rental] = availabilityScenario();
    availabilityReservation($equipment, 6, 8); // free until day +5

    availabilityRequest($this, $renter, $rental, 7);

    $message = session('errors')->first('new_end_date');
    expect($message)->toContain(today()->addDays(5)->format('d/m/Y'));
});

// ---------------------------------------------------------------------------
// Approval: a booking may appear between the request and the decision
// ---------------------------------------------------------------------------

test('the owner cannot approve an extension if a booking appeared in the meantime', function () {
    Event::fake([ExtensionApproved::class]);
    ['owner' => $owner, 'equipment' => $equipment, 'rental' => $rental] = availabilityScenario();

    $extension = RentalExtension::factory()->create([
        'rental_id' => $rental->id,
        'new_end_date' => today()->addDays(6)->toDateString(),
        'additional_amount' => 30,
    ]);
    $oldEnd = $rental->end_date->copy();

    availabilityReservation($equipment, 5, 7); // booked after the request was made

    $this->actingAs($owner)
        ->post(route('rental.extensions.approve', $extension))
        ->assertSessionHasErrors('extension');

    expect($rental->refresh()->end_date->isSameDay($oldEnd))->toBeTrue()
        ->and($extension->refresh()->status->value)->toBe('pending');
});

test('the administrator cannot approve an extension if a booking appeared in the meantime', function () {
    Event::fake([ExtensionApproved::class]);
    ['equipment' => $equipment, 'rental' => $rental] = availabilityScenario();
    $admin = availabilityUser(UserRole::ADMIN);

    $extension = RentalExtension::factory()->create([
        'rental_id' => $rental->id,
        'new_end_date' => today()->addDays(6)->toDateString(),
        'additional_amount' => 30,
    ]);

    availabilityReservation($equipment, 5, 7);

    $this->actingAs($admin)
        ->post(route('admin.rental-extensions.approve', $extension))
        ->assertSessionHasErrors('extension');

    expect($extension->refresh()->status->value)->toBe('pending');
});

// ---------------------------------------------------------------------------
// Decision aid
// ---------------------------------------------------------------------------

test('the decision aid recommends approving a clean request', function () {
    ['rental' => $rental] = availabilityScenario();
    $extension = RentalExtension::factory()->create([
        'rental_id' => $rental->id,
        'new_end_date' => today()->addDays(6)->toDateString(),
    ]);

    $advice = app(ExtensionAdvisorService::class)->advise($extension);

    expect($advice['recommendation'])->toBe('approve');
});

test('the decision aid recommends refusing when the equipment is booked', function () {
    ['equipment' => $equipment, 'rental' => $rental] = availabilityScenario();
    $extension = RentalExtension::factory()->create([
        'rental_id' => $rental->id,
        'new_end_date' => today()->addDays(6)->toDateString(),
    ]);
    availabilityReservation($equipment, 5, 7);

    $advice = app(ExtensionAdvisorService::class)->advise($extension);

    expect($advice['recommendation'])->toBe('refuse')
        ->and(collect($advice['reasons'])->pluck('level')->all())->toContain('blocker');
});

test('the decision aid asks for a review when the rental is already late', function () {
    ['rental' => $rental] = availabilityScenario();
    $rental->update([
        'start_date' => today()->subDays(8)->toDateString(),
        'end_date' => today()->subDays(2)->toDateString(),
    ]);
    $extension = RentalExtension::factory()->create([
        'rental_id' => $rental->id,
        'new_end_date' => today()->addDay()->toDateString(),
    ]);

    $advice = app(ExtensionAdvisorService::class)->advise($extension);

    expect($advice['recommendation'])->toBe('review');
});

test('the decision aid asks for a review when the extension is very long', function () {
    ['rental' => $rental] = availabilityScenario();
    $extension = RentalExtension::factory()->create([
        'rental_id' => $rental->id,
        'new_end_date' => today()->addDays(15)->toDateString(), // 12 extra days
    ]);

    $advice = app(ExtensionAdvisorService::class)->advise($extension);

    expect($advice['recommendation'])->toBe('review');
});

test('the admin extension page shows the decision aid for a pending request', function () {
    ['rental' => $rental] = availabilityScenario();
    $admin = availabilityUser(UserRole::ADMIN);
    $extension = RentalExtension::factory()->create([
        'rental_id' => $rental->id,
        'new_end_date' => today()->addDays(6)->toDateString(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.rental-extensions.show', $extension))
        ->assertOk()
        ->assertSee('Decision aid')
        ->assertSee('Recommendation');
});