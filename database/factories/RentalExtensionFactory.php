<?php

namespace Database\Factories;

use App\Enums\ExtensionStatus;
use App\Models\Rental;
use App\Models\RentalExtension;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Builds FAKE extension requests.
 * The old end date is read from the rental, so the data stays coherent:
 * the request always starts where the rental currently ends.
 * Usage examples:
 *   RentalExtension::factory()->create(['rental_id' => $rental->id]);            // pending
 *   RentalExtension::factory()->approved()->create(['rental_id' => $rental->id]);
 *
 * NOTE: the "approved" state only sets the status. The seeder is the one that
 * also updates the rental (like the controller does when an admin approves).
 *
 * @extends Factory<RentalExtension>
 */
class RentalExtensionFactory extends Factory
{
    /** The model this factory builds. */
    protected $model = RentalExtension::class;

    public function definition(): array
    {
        // Number of extra days, chosen once so the end date and the amount match
        $extraDays = fake()->numberBetween(1, 5);

        return [
            // A new rental is created automatically unless rental_id is given
            'rental_id' => Rental::factory(),

            'requested_date' => now()->subDays(fake()->numberBetween(0, 5))->toDateString(),

            // The closures receive the attributes already computed (rental_id is ready here)
            'old_end_date' => fn (array $attributes) => Rental::find($attributes['rental_id'])->end_date,

            'new_end_date' => fn (array $attributes) => Rental::find($attributes['rental_id'])
                ->end_date->copy()->addDays($extraDays),

            // Amount = rental daily rate x extra days
            'additional_amount' => function (array $attributes) use ($extraDays) {
                $rental = Rental::find($attributes['rental_id']);
                $rentalDays = max(1, (int) $rental->start_date->diffInDays($rental->end_date));

                return round(((float) $rental->total_amount / $rentalDays) * $extraDays, 2);
            },

            'reason' => fake()->optional(0.8)->randomElement([
                'I need the equipment for one more day.',
                'My trip was extended.',
                'The weather was bad, I could not use it as planned.',
                'I am waiting for my own equipment to be repaired.',
            ]),

            'status' => ExtensionStatus::PENDING->value,
        ];
    }

    // ------------------------------------------------------------------
    // States
    // ------------------------------------------------------------------

    /** Accepted request. */
    public function approved(): static
    {
        return $this->state(['status' => ExtensionStatus::APPROVED->value]);
    }

    /** Refused request. */
    public function rejected(): static
    {
        return $this->state(['status' => ExtensionStatus::REJECTED->value]);
    }
}
