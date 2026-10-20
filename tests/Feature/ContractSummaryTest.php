<?php

use App\Enums\ContractStatus;
use App\Enums\RentalStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\RentalContract;
use App\Models\User;
use App\Services\ContractSummaryService;
use Illuminate\Support\Facades\Http;

// ---------------------------------------------------------------------------
// Helpers (names start with "summary" to avoid clashes between test files).
// No test calls the real Groq service: Http::fake() answers instead of it.
// ---------------------------------------------------------------------------

/** A renter, an owner (with an equipment) and a draft contract. */
function summaryScenario(): array
{
    $owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true]);
    $renter = User::factory()->create(['role' => UserRole::BUYER, 'role_setup_completed' => true]);

    $equipment = Equipment::factory()->create([
        'owner_id' => $owner->id,
        'category_id' => Category::factory()->create(['status' => 'active'])->id,
        'approval_status' => 'published',
        'status' => 'available',
    ]);

    $rental = Rental::factory()->create([
        'user_id' => $renter->id,
        'equipment_id' => $equipment->id,
        'status' => RentalStatus::PENDING->value,
        'start_date' => today()->addDays(2)->toDateString(),
        'end_date' => today()->addDays(5)->toDateString(),
        'total_amount' => 90,
    ]);

    $contract = RentalContract::factory()->create([
        'rental_id' => $rental->id,
        'contract_status' => ContractStatus::DRAFT->value,
        'signed_at' => null,
    ]);

    return compact('owner', 'renter', 'equipment', 'rental', 'contract');
}

/** The 5 points that the fake AI returns. */
function summaryPoints(): array
{
    return [
        'You must return the equipment on the last day of the rental.',
        'You pay the rental amount shown in the contract.',
        'The security deposit is kept until the equipment is back.',
        'You are responsible for any damage or loss.',
        'Late returns are charged by the day.',
    ];
}

/** Makes every call to Groq return the given content, as the real API would. */
function summaryFakeGroq(?string $content = null, int $status = 200): void
{
    Http::fake([
        'api.groq.com/*' => Http::response(
            ['choices' => [['message' => ['content' => $content ?? json_encode(['points' => summaryPoints()])]]]],
            $status
        ),
    ]);
}

beforeEach(function () {
    // A key is needed for the feature to be "available" (it is never sent anywhere in the tests)
    config(['groq.key' => 'test-key']);
});

// ---------------------------------------------------------------------------
// The summary
// ---------------------------------------------------------------------------

test('the renter can ask for a plain-language summary and sees it on the contract page', function () {
    summaryFakeGroq();
    ['renter' => $renter, 'contract' => $contract] = summaryScenario();

    $this->actingAs($renter)
        ->post(route('contracts.summary', $contract))
        ->assertRedirect(route('contracts.show', $contract))
        ->assertSessionHas('status', 'contract-summary-ready');

    $this->actingAs($renter)
        ->get(route('contracts.show', $contract))
        ->assertOk()
        ->assertSee('Contract in simple words')
        ->assertSee(summaryPoints()[0])
        ->assertSee('not legal advice');
});

test('the summary of a contract is generated only once', function () {
    summaryFakeGroq();
    ['renter' => $renter, 'contract' => $contract] = summaryScenario();

    $this->actingAs($renter)->post(route('contracts.summary', $contract));
    $this->actingAs($renter)->post(route('contracts.summary', $contract));

    // The second request was answered from the cache: Groq was called only once
    Http::assertSentCount(1);
});

test('a model answer wrapped in a markdown block is still understood', function () {
    summaryFakeGroq("Here is the summary:\n```json\n" . json_encode(['points' => summaryPoints()]) . "\n```");
    ['renter' => $renter, 'contract' => $contract] = summaryScenario();

    $this->actingAs($renter)->post(route('contracts.summary', $contract));

    expect(app(ContractSummaryService::class)->cached($contract, 'en')['points'])->toHaveCount(5);
});

test('only the contract terms are sent to the AI, never personal data', function () {
    summaryFakeGroq();
    ['renter' => $renter, 'owner' => $owner, 'contract' => $contract] = summaryScenario();

    $this->actingAs($renter)->post(route('contracts.summary', $contract));

    Http::assertSent(function ($request) use ($renter, $owner) {
        $body = $request->body();

        return str_contains($body, 'Contract terms')          // the contract is sent
            && ! str_contains($body, $renter->email)           // but no e-mail
            && ! str_contains($body, $owner->email);
    });
});

// ---------------------------------------------------------------------------
// Failures and access
// ---------------------------------------------------------------------------

test('an AI failure shows a friendly message and nothing is cached', function () {
    summaryFakeGroq('{"error":"boom"}', 500);
    ['renter' => $renter, 'contract' => $contract] = summaryScenario();

    $this->actingAs($renter)
        ->post(route('contracts.summary', $contract))
        ->assertRedirect(route('contracts.show', $contract))
        ->assertSessionHasErrors('summary');

    // A failed call is not saved: the next attempt can work
    expect(app(ContractSummaryService::class)->cached($contract, 'en'))->toBeNull();
});

test('a rate limit of the AI service is explained to the user', function () {
    summaryFakeGroq('{"error":"rate limit"}', 429);
    ['renter' => $renter, 'contract' => $contract] = summaryScenario();

    $this->actingAs($renter)->post(route('contracts.summary', $contract));

    expect(session('errors')->first('summary'))->toContain('busy');
});

test('a missing API key is handled without calling the service', function () {
    config(['groq.key' => null]);
    Http::fake();
    ['renter' => $renter, 'contract' => $contract] = summaryScenario();

    $this->actingAs($renter)
        ->post(route('contracts.summary', $contract))
        ->assertSessionHasErrors('summary');

    Http::assertNothingSent();
});

test('the summary button is hidden when the AI service is not configured', function () {
    ['renter' => $renter, 'contract' => $contract] = summaryScenario();

    $this->actingAs($renter)
        ->get(route('contracts.show', $contract))
        ->assertSee('Explain this contract in simple words');

    config(['groq.key' => null]);

    $this->actingAs($renter)
        ->get(route('contracts.show', $contract))
        ->assertDontSee('Explain this contract in simple words');
});

test('the owner and the admin can ask for the summary, a stranger cannot', function () {
    summaryFakeGroq();
    ['owner' => $owner, 'contract' => $contract] = summaryScenario();
    $admin = User::factory()->create(['role' => UserRole::ADMIN, 'role_setup_completed' => true]);
    $stranger = User::factory()->create(['role' => UserRole::BUYER, 'role_setup_completed' => true]);

    $this->actingAs($owner)->post(route('contracts.summary', $contract))->assertRedirect();
    $this->actingAs($admin)->post(route('contracts.summary', $contract))->assertRedirect();
    $this->actingAs($stranger)->post(route('contracts.summary', $contract))->assertForbidden();
});