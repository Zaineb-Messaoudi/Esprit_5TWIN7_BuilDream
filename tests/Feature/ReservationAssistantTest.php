<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\Reservation;
use App\Models\ReservationAssistantRequest;
use App\Models\User;
use Illuminate\Support\Carbon;

test('buyer sees work-request and equipment-reservation assistants', function () {
    $buyer = User::factory()->create(['role' => UserRole::BUYER]);

    $this->actingAs($buyer)
        ->get(route('front.reservation-assistant'))
        ->assertOk()
        ->assertSee('Tell us what you need')
        ->assertSee('Find the best equipment for your trip')
        ->assertSee('City for artisan recommendations')
        ->assertSee('Show recommendations')
        ->assertSee('Confirm and send request');
});

test('recommendations rank published matching equipment by owner rating and exclude unavailable listings', function () {
    $buyer = User::factory()->create(['role' => UserRole::BUYER]);
    $category = Category::factory()->create(['name' => 'Portable batteries', 'status' => 'active']);
    $trustedOwner = User::factory()->create([
        'role' => UserRole::OWNER,
        'aggregate_rating' => 4.8,
        'reviews_count' => 10,
    ]);
    $newOwner = User::factory()->create([
        'role' => UserRole::OWNER,
        'aggregate_rating' => null,
        'reviews_count' => 0,
    ]);
    $equipment = Equipment::factory()->create([
        'category_id' => $category->id,
        'owner_id' => $trustedOwner->id,
        'name' => 'Rated battery in Tunis',
        'location' => 'Tunis',
        'price_per_day' => 40,
        'approval_status' => 'published',
        'status' => 'available',
    ]);
    $newListing = Equipment::factory()->create([
        'category_id' => $category->id,
        'owner_id' => $newOwner->id,
        'name' => 'New battery in Tunis',
        'location' => 'Tunis',
        'price_per_day' => 30,
        'approval_status' => 'published',
        'status' => 'available',
    ]);
    $wrongCity = Equipment::factory()->create([
        'category_id' => $category->id,
        'location' => 'Sousse',
        'approval_status' => 'published',
        'status' => 'available',
    ]);
    $overBudget = Equipment::factory()->create([
        'category_id' => $category->id,
        'location' => 'Tunis',
        'price_per_day' => 100,
        'approval_status' => 'published',
        'status' => 'available',
    ]);
    $start = Carbon::tomorrow()->toDateString();
    $end = Carbon::tomorrow()->addDays(2)->toDateString();

    Reservation::factory()->create([
        'equipment_id' => $overBudget->id,
        'start_date' => $start,
        'end_date' => $end,
        'status' => 'confirmed',
    ]);

    $this->actingAs($buyer)
        ->postJson(route('front.reservation-assistant.recommend'), [
            'category_id' => $category->id,
            'location' => 'Tunis',
            'max_daily_budget' => 50,
            'start_date' => $start,
            'end_date' => $end,
        ])
        ->assertOk()
        ->assertJsonCount(2, 'recommendations')
        ->assertJsonPath('recommendations.0.id', $equipment->id)
        ->assertJsonPath('recommendations.0.owner', $trustedOwner->name)
        ->assertJsonPath('recommendations.0.rating', 4.8)
        ->assertJsonPath('recommendations.1.id', $newListing->id)
        ->assertJsonPath('recommendations.0.score', 91)
        ->assertJsonPath('recommendations.0.reservation_url', route('front.reserve', [
            'equipment' => $equipment->id,
            'start' => $start,
            'end' => $end,
        ]));
});

test('recommendations exclude listings with an overlapping rental', function () {
    $buyer = User::factory()->create(['role' => UserRole::BUYER]);
    $owner = User::factory()->create(['role' => UserRole::OWNER]);
    $renter = User::factory()->create(['role' => UserRole::BUYER]);
    $category = Category::factory()->create(['status' => 'active']);
    $booked = Equipment::factory()->create([
        'category_id' => $category->id,
        'owner_id' => $owner->id,
        'location' => 'Tunis',
        'approval_status' => 'published',
        'status' => 'available',
    ]);
    $available = Equipment::factory()->create([
        'category_id' => $category->id,
        'owner_id' => $owner->id,
        'location' => 'Tunis',
        'approval_status' => 'published',
        'status' => 'available',
    ]);
    $start = Carbon::tomorrow()->toDateString();
    $end = Carbon::tomorrow()->addDays(2)->toDateString();
    Rental::factory()->create([
        'equipment_id' => $booked->id,
        'user_id' => $renter->id,
        'start_date' => $start,
        'end_date' => $end,
        'status' => 'pending',
    ]);

    $this->actingAs($buyer)
        ->postJson(route('front.reservation-assistant.recommend'), [
            'category_id' => $category->id,
            'location' => 'Tunis',
            'start_date' => $start,
            'end_date' => $end,
        ])
        ->assertOk()
        ->assertJsonCount(1, 'recommendations')
        ->assertJsonPath('recommendations.0.id', $available->id);
});

test('buyer can analyze a request and receives structured fields', function () {
    $buyer = User::factory()->create(['role' => UserRole::BUYER]);

    $this->actingAs($buyer)
        ->postJson(route('front.reservation-assistant.analyze'), [
            'description' => 'Je veux de la plomberie urgente, surface 12 m2, budget 900 TND, adresse : 12 rue du Lac, Tunis.',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'ready')
        ->assertJsonPath('fields.service_type', 'plumbing')
        ->assertJsonPath('fields.surface_m2', 12)
        ->assertJsonPath('fields.urgency', 'urgent')
        ->assertJsonPath('fields.budget', 900)
        ->assertJsonPath('fields.address', '12 rue du Lac, Tunis')
        ->assertJsonPath('fields.city', 'Tunis');
});

test('buyer can edit the analyzed details and must confirm before saving a request', function () {
    $buyer = User::factory()->create(['role' => UserRole::BUYER]);
    $request = [
        'service_type' => 'painting',
        'custom_description' => '',
        'surface_m2' => '30',
        'urgency' => 'soon',
        'budget' => '1500',
        'address' => '4 avenue Habib Bourguiba, Tunis',
        'city' => 'Tunis',
    ];

    $this->actingAs($buyer)
        ->from(route('front.reservation-assistant'))
        ->post(route('front.reservation-assistant.store'), $request)
        ->assertSessionHasErrors('confirmed');

    $this->post(route('front.reservation-assistant.store'), [...$request, 'confirmed' => '1'])
        ->assertRedirect(route('front.reservation-assistant'))
        ->assertSessionHas('requestReference');

    $this->assertDatabaseHas('reservation_assistant_requests', [
        'user_id' => $buyer->id,
        'service_type' => 'painting',
        'surface_m2' => '30.00',
        'urgency' => 'soon',
        'budget' => '1500.00',
        'address' => '4 avenue Habib Bourguiba, Tunis',
        'city' => 'Tunis',
        'status' => 'pending',
    ]);
});

test('buyer requests outside the catalog can be submitted as a custom quote', function () {
    $buyer = User::factory()->create(['role' => UserRole::BUYER]);

    $this->actingAs($buyer)
        ->post(route('front.reservation-assistant.store'), [
            'service_type' => 'custom_quote',
            'custom_description' => 'Installation d’une piscine extérieure',
            'surface_m2' => '35',
            'urgency' => 'flexible',
            'budget' => '8000',
            'address' => '10 rue des Orangers, Mahdia',
            'city' => 'Mahdia',
            'confirmed' => '1',
        ])
        ->assertRedirect(route('front.reservation-assistant'));

    expect(ReservationAssistantRequest::query()->where('user_id', $buyer->id)->value('service_type'))
        ->toBe('custom_quote');
});

test('owner cannot submit a buyer work request through the assistant', function () {
    $owner = User::factory()->create(['role' => UserRole::OWNER]);

    $this->actingAs($owner)
        ->get(route('front.reservation-assistant'))
        ->assertForbidden();
});

test('owner can create and edit a separate artisan profile', function () {
    $owner = User::factory()->create(['role' => UserRole::OWNER]);

    $this->actingAs($owner)
        ->get(route('front.owner-service-provider-profile'))
        ->assertOk()
        ->assertSee('Artisan profile');

    $this->put(route('front.owner-service-provider-profile.update'), [
        'business_name' => 'Plomberie Ben Salem',
        'description' => 'Plomberie and maintenance',
        'specialties' => ['plumbing', 'renovation'],
        'service_areas' => 'Tunis, Ariana',
        'experience_years' => 8,
        'minimum_project_budget' => 250,
        'accepting_requests' => '1',
        'available_from' => Carbon::today()->toDateString(),
        'contact_email' => 'contact@example.test',
    ])->assertRedirect(route('front.owner-service-provider-profile'));

    $this->assertDatabaseHas('service_provider_profiles', [
        'user_id' => $owner->id,
        'business_name' => 'Plomberie Ben Salem',
        'accepting_requests' => true,
    ]);

    expect($owner->fresh()->serviceProviderProfile->service_areas)->toBe(['Tunis', 'Ariana'])
        ->and($owner->fresh()->serviceProviderProfile->specialties)->toBe(['plumbing', 'renovation']);
});

test('service recommendations rank eligible artisan profiles and do not book automatically', function () {
    $buyer = User::factory()->create(['role' => UserRole::BUYER]);
    $ratedArtisan = User::factory()->create([
        'role' => UserRole::OWNER,
        'aggregate_rating' => 4.8,
        'reviews_count' => 10,
    ]);
    $newArtisan = User::factory()->create(['role' => UserRole::OWNER]);
    $wrongCityArtisan = User::factory()->create(['role' => UserRole::OWNER]);
    $futureArtisan = User::factory()->create(['role' => UserRole::OWNER]);
    $overBudgetArtisan = User::factory()->create(['role' => UserRole::OWNER]);

    $baseProfile = [
        'business_name' => 'Plombier de Tunis',
        'specialties' => ['plumbing'],
        'service_areas' => ['Tunis'],
        'experience_years' => 5,
        'minimum_project_budget' => 200,
        'available_from' => Carbon::today()->toDateString(),
        'accepting_requests' => true,
    ];
    foreach ([$ratedArtisan, $newArtisan, $wrongCityArtisan, $futureArtisan, $overBudgetArtisan] as $artisan) {
        $artisan->serviceProviderProfile()->create($baseProfile);
    }
    $wrongCityArtisan->serviceProviderProfile()->update(['service_areas' => ['Sousse']]);
    $futureArtisan->serviceProviderProfile()->update(['available_from' => Carbon::tomorrow()->toDateString()]);
    $overBudgetArtisan->serviceProviderProfile()->update(['minimum_project_budget' => 1000]);
    $ratedArtisan->serviceProviderProfile()->update(['contact_email' => 'artisan@example.test']);

    $workRequest = ReservationAssistantRequest::create([
        'reference' => 'DEV-TEST1234',
        'user_id' => $buyer->id,
        'service_type' => 'plumbing',
        'urgency' => 'urgent',
        'budget' => 900,
        'address' => '12 rue du Lac, Tunis',
        'city' => 'Tunis',
        'status' => 'pending',
    ]);

    $this->actingAs($buyer)
        ->postJson(route('front.reservation-assistant.service-providers', $workRequest))
        ->assertOk()
        ->assertJsonCount(2, 'recommendations')
        ->assertJsonPath('recommendations.0.name', $ratedArtisan->name)
        ->assertJsonPath('recommendations.0.contact_email', 'artisan@example.test')
        ->assertJsonPath('recommendations.1.name', $newArtisan->name);

    $this->withSession(['requestReference' => $workRequest->reference])
        ->get(route('front.reservation-assistant'))
        ->assertOk()
        ->assertSee('Find a suitable artisan')
        ->assertSee('Location uses the artisan’s declared service areas');

    $this->assertDatabaseCount('reservations', 0);
});

test('buyers cannot request artisan recommendations for another buyer request', function () {
    $buyer = User::factory()->create(['role' => UserRole::BUYER]);
    $otherBuyer = User::factory()->create(['role' => UserRole::BUYER]);
    $workRequest = ReservationAssistantRequest::create([
        'reference' => 'DEV-OTHER123',
        'user_id' => $otherBuyer->id,
        'service_type' => 'plumbing',
        'urgency' => 'urgent',
        'budget' => 900,
        'address' => '12 rue du Lac, Tunis',
        'city' => 'Tunis',
        'status' => 'pending',
    ]);

    $this->actingAs($buyer)
        ->postJson(route('front.reservation-assistant.service-providers', $workRequest))
        ->assertNotFound();
});
