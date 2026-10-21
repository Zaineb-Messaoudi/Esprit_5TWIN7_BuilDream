<?php

use App\Enums\ContractStatus;
use App\Enums\DepositStatus;
use App\Enums\RentalStatus;
use App\Enums\UserRole;
use App\Events\ExtensionApproved;
use App\Events\ExtensionRequested;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\RentalContract;
use App\Models\RentalExtension;
use App\Models\User;
use Illuminate\Support\Facades\Event;

// ---------------------------------------------------------------------------
// Helpers (names start with "contractSigning" to avoid clashes between test files)
// ---------------------------------------------------------------------------

/** A user with the given role and a completed role setup. */
function contractSigningUser(UserRole $role): User
{
    return User::factory()->create([
        'role' => $role,
        'role_setup_completed' => true,
    ]);
}

/**
 * Builds a complete scenario: an owner with an equipment, a renter, a rental and its DRAFT contract.
 * $rentalOverrides / $contractOverrides replace the default values (the left side of "+" wins).
 */
function contractSigningScenario(array $rentalOverrides = [], array $contractOverrides = []): array
{
    $owner = contractSigningUser(UserRole::OWNER);
    $renter = contractSigningUser(UserRole::BUYER);

    $equipment = Equipment::factory()->create([
        'owner_id' => $owner->id,
        'category_id' => Category::factory()->create(['status' => 'active'])->id,
        'approval_status' => 'published',
        'status' => 'available',
    ]);

    $rental = Rental::factory()->create($rentalOverrides + [
        'user_id' => $renter->id,
        'equipment_id' => $equipment->id,
        'start_date' => today()->addDays(2)->toDateString(),
        'end_date' => today()->addDays(5)->toDateString(),
        'total_amount' => 90,
        'status' => RentalStatus::PENDING->value,
    ]);

    $contract = RentalContract::factory()->create($contractOverrides + [
        'rental_id' => $rental->id,
        'contract_status' => ContractStatus::DRAFT->value,
        'signed_at' => null,
        'deposit_status' => DepositStatus::PENDING->value,
    ]);

    return compact('owner', 'renter', 'equipment', 'rental', 'contract');
}

/** Same scenario, but with an ACTIVE rental (needed for extension requests). */
function contractSigningActiveScenario(): array
{
    return contractSigningScenario([
        'status' => RentalStatus::ACTIVE->value,
        'start_date' => today()->subDays(2)->toDateString(),
        'end_date' => today()->addDays(3)->toDateString(),
    ]);
}

// ---------------------------------------------------------------------------
// Read first, then sign
// ---------------------------------------------------------------------------

test('the renter must read the contract before signing it', function () {
    ['renter' => $renter, 'contract' => $contract] = contractSigningScenario();

    // The form is sent directly, without ever opening the contract page
    $this->actingAs($renter)
        ->post(route('contracts.accept', $contract), ['accept_terms' => '1'])
        ->assertRedirect(route('contracts.show', $contract))
        ->assertSessionHasErrors('accept_terms');

    expect($contract->refresh()->contract_status)->toBe(ContractStatus::DRAFT);
});

test('opening the contract page records that the renter read it', function () {
    ['renter' => $renter, 'contract' => $contract] = contractSigningScenario();

    $this->actingAs($renter)
        ->get(route('contracts.show', $contract))
        ->assertOk()
        ->assertSee($contract->contract_number)
        ->assertSessionHas('contract_read.'.$contract->id);
});

test('the renter reads the contract, ticks the box and signs it', function () {
    ['renter' => $renter, 'contract' => $contract] = contractSigningScenario();

    // withSession() puts the "contract was read" mark in the session explicitly
    $this->actingAs($renter)
        ->withSession(['contract_read.'.$contract->id => now()->timestamp])
        ->post(route('contracts.accept', $contract), ['accept_terms' => '1'])
        ->assertRedirect(route('contracts.show', $contract))
        ->assertSessionHas('status', 'contract-signed');

    $contract->refresh();
    expect($contract->contract_status)->toBe(ContractStatus::SIGNED)
        ->and($contract->signed_at)->not->toBeNull();
});

test('signing needs the accept checkbox', function () {
    ['renter' => $renter, 'contract' => $contract] = contractSigningScenario();

    $this->actingAs($renter)
        ->withSession(['contract_read.'.$contract->id => now()->timestamp])
        ->post(route('contracts.accept', $contract), [])
        ->assertSessionHasErrors('accept_terms');

    expect($contract->refresh()->contract_status)->toBe(ContractStatus::DRAFT);
});

test('declining the contract terminates it and cancels the rental', function () {
    ['renter' => $renter, 'rental' => $rental, 'contract' => $contract] = contractSigningScenario();

    $this->actingAs($renter)
        ->post(route('contracts.decline', $contract))
        ->assertRedirect(route('contracts.show', $contract))
        ->assertSessionHas('status', 'contract-declined');

    expect($contract->refresh()->contract_status)->toBe(ContractStatus::TERMINATED)
        ->and($rental->refresh()->status)->toBe(RentalStatus::CANCELLED);
});

test('the old one-click signing route no longer signs the contract', function () {
    ['renter' => $renter, 'contract' => $contract] = contractSigningScenario();

    $this->actingAs($renter)
        ->post(route('buyer.contracts.sign', $contract))
        ->assertRedirect(route('contracts.show', $contract));

    expect($contract->refresh()->contract_status)->toBe(ContractStatus::DRAFT);
});

// ---------------------------------------------------------------------------
// Who can see and sign a contract
// ---------------------------------------------------------------------------

test('the owner and the admin can read the contract but never sign it', function () {
    ['owner' => $owner, 'contract' => $contract] = contractSigningScenario();
    $admin = contractSigningUser(UserRole::ADMIN);
    $read = ['contract_read.'.$contract->id => now()->timestamp];

    foreach ([$owner, $admin] as $user) {
        $this->actingAs($user)
            ->get(route('contracts.show', $contract))
            ->assertOk();

        // Even with the "read" mark and the checkbox, only the renter may sign
        $this->actingAs($user)
            ->withSession($read)
            ->post(route('contracts.accept', $contract), ['accept_terms' => '1'])
            ->assertForbidden();
    }

    expect($contract->refresh()->contract_status)->toBe(ContractStatus::DRAFT);
});

test('another user cannot read the contract', function () {
    ['contract' => $contract] = contractSigningScenario();
    $stranger = contractSigningUser(UserRole::BUYER);

    $this->actingAs($stranger)
        ->get(route('contracts.show', $contract))
        ->assertForbidden();
});

test('a guest is sent to the login page', function () {
    ['contract' => $contract] = contractSigningScenario();

    $this->get(route('contracts.show', $contract))
        ->assertRedirect(route('login'));
});

// ---------------------------------------------------------------------------
// Deposit: only the owner of the equipment (or an admin) may manage it
// ---------------------------------------------------------------------------

test('an owner cannot manage the deposit of another owner contract', function () {
    ['contract' => $contract] = contractSigningScenario();
    $otherOwner = contractSigningUser(UserRole::OWNER);

    $this->actingAs($otherOwner)
        ->post(route('owner.rental-contracts.deposit.hold', $contract))
        ->assertForbidden();

    expect($contract->refresh()->deposit_status)->toBe(DepositStatus::PENDING);
});

test('the equipment owner can hold the deposit of his own contract', function () {
    ['owner' => $owner, 'contract' => $contract] = contractSigningScenario();

    $this->actingAs($owner)
        ->post(route('owner.rental-contracts.deposit.hold', $contract))
        ->assertSessionHas('status', 'deposit-held');

    expect($contract->refresh()->deposit_status)->toBe(DepositStatus::HELD);
});

// ---------------------------------------------------------------------------
// Front office extension requests
// ---------------------------------------------------------------------------

test('a renter cannot send two pending extension requests for the same rental', function () {
    // Events are faked: no notification or broadcast is really sent during the test
    Event::fake([ExtensionRequested::class]);
    ['renter' => $renter, 'rental' => $rental] = contractSigningActiveScenario();

    $this->actingAs($renter)
        ->post(route('rental.extensions.store', $rental), ['new_end_date' => today()->addDays(5)->toDateString()])
        ->assertRedirect();

    expect(RentalExtension::where('rental_id', $rental->id)->count())->toBe(1);

    // The second request is refused
    $this->actingAs($renter)
        ->post(route('rental.extensions.store', $rental), ['new_end_date' => today()->addDays(6)->toDateString()])
        ->assertSessionHasErrors('new_end_date');

    expect(RentalExtension::where('rental_id', $rental->id)->count())->toBe(1);
});

test('the extension form redirects instead of crashing when a request is already pending', function () {
    ['renter' => $renter, 'rental' => $rental] = contractSigningActiveScenario();
    RentalExtension::factory()->create(['rental_id' => $rental->id]); // pending by default

    $this->actingAs($renter)
        ->get(route('rental.extensions.create', $rental))
        ->assertRedirect(route('front.my-rental-detail', $rental->reference));
});

test('the equipment owner approves an extension and the rental is updated', function () {
    Event::fake([ExtensionApproved::class]);
    ['owner' => $owner, 'rental' => $rental] = contractSigningActiveScenario();

    $extension = RentalExtension::factory()->create([
        'rental_id' => $rental->id,
        'new_end_date' => $rental->end_date->copy()->addDays(2),
        'additional_amount' => 40,
    ]);
    $oldEnd = $rental->end_date;

    $this->actingAs($owner)
        ->post(route('rental.extensions.approve', $extension))
        ->assertRedirect();

    expect($rental->refresh()->end_date->isAfter($oldEnd))->toBeTrue()
        ->and((float) $rental->total_amount)->toBe(130.0); // 90 + 40
});

test('a renter cannot approve his own extension request', function () {
    ['renter' => $renter, 'rental' => $rental] = contractSigningActiveScenario();
    $extension = RentalExtension::factory()->create(['rental_id' => $rental->id]);

    $this->actingAs($renter)
        ->post(route('rental.extensions.approve', $extension))
        ->assertForbidden();
});
