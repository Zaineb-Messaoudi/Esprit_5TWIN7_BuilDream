<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\RentalExtension;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerWorkspaceBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_rental_history_uses_persisted_rental_and_equipment_data(): void
    {
        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $equipment = Equipment::factory()->create(['name' => 'Real database battery']);

        $rental = Rental::factory()->active()->create([
            'user_id' => $buyer->id,
            'equipment_id' => $equipment->id,
            'reference' => 'RNT-REAL-001',
            'total_amount' => 77.50,
        ]);

        $this->actingAs($buyer)
            ->get(route('front.my-rentals'))
            ->assertOk()
            ->assertSee('Live account data.')
            ->assertSee($rental->reference)
            ->assertSee('Real database battery')
            ->assertSee('77.50 TND');
    }

    public function test_buyer_extension_history_uses_persisted_extension_data(): void
    {
        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $rental = Rental::factory()->active()->create(['user_id' => $buyer->id]);

        RentalExtension::factory()->approved()->create([
            'rental_id' => $rental->id,
            'additional_amount' => 19.50,
        ]);

        $this->actingAs($buyer)
            ->get(route('front.my-extensions'))
            ->assertOk()
            ->assertSee('Live account data.')
            ->assertSee($rental->reference)
            ->assertSee('19.50 TND')
            ->assertSee('Approved');
    }
}
