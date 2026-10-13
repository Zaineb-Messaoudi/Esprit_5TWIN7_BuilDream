<?php

namespace Database\Factories;

use App\Enums\ContractStatus;
use App\Models\Rental;
use App\Models\RentalContract;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Builds FAKE rental contracts.
 * A contract needs a rental (1-1 relation): if none is given, a new rental is created.
 * Usage examples:
 *   RentalContract::factory()->signed()->create(['rental_id' => $rental->id]);
 *
 * @extends Factory<RentalContract>
 */
class RentalContractFactory extends Factory
{
    /** The model this factory builds. */
    protected $model = RentalContract::class;

    /** Default values: a signed contract. */
    public function definition(): array
    {
        return [
            // A new rental is created automatically unless rental_id is given
            'rental_id' => Rental::factory(),
            // Temporary unique value: configure() below replaces it with CTR-2026-0001...
            'contract_number' => 'TMP-'.Str::uuid(),
            'signed_at' => now()->subDays(fake()->numberBetween(0, 10)),
            'terms' => fake()->randomElement([
                'The renter agrees to use the equipment with care and to return it on the end date in the same condition.',
                'The equipment must be used only for its normal purpose. Any damage or loss is the responsibility of the renter.',
                'The renter must return the equipment fully charged and clean. Late returns are charged by the day.',
            ]),
            'deposit_amount' => fake()->randomElement([0, 20, 50, 100]),
            'contract_status' => ContractStatus::SIGNED->value,
        ];
    }

    /** After a contract is saved, give it its final number (same format as the controller). */
    public function configure(): static
    {
        return $this->afterCreating(function (RentalContract $contract) {
            $contract->update([
                'contract_number' => sprintf('CTR-%d-%04d', now()->year, $contract->id),
            ]);
        });
    }

    // ------------------------------------------------------------------
    // States
    // ------------------------------------------------------------------

    /** Not signed yet: a draft has no signature date. */
    public function draft(): static
    {
        return $this->state([
            'contract_status' => ContractStatus::DRAFT->value,
            'signed_at' => null,
        ]);
    }

    /** Signed by the renter. */
    public function signed(): static
    {
        return $this->state([
            'contract_status' => ContractStatus::SIGNED->value,
            'signed_at' => now()->subDays(fake()->numberBetween(0, 10)),
        ]);
    }

    /** Ended: it was signed in the past. */
    public function terminated(): static
    {
        return $this->state([
            'contract_status' => ContractStatus::TERMINATED->value,
            'signed_at' => now()->subDays(fake()->numberBetween(15, 40)),
        ]);
    }
}
