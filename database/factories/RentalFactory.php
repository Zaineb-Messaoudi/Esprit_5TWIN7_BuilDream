<?php

namespace Database\Factories;

use App\Enums\RentalStatus;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Factory = a recipe that builds FAKE rentals for tests and demo data.
 * Usage examples:
 *   Rental::factory()->create();                  // one random rental
 *   Rental::factory()->count(5)->active()->create();  // five active rentals
 *
 * @extends Factory<Rental>
 */
class RentalFactory extends Factory
{
    /** The model this factory builds. */
    protected $model = Rental::class;

    /**
     * Default values of a rental. The dates are random, and the status is
     * deduced from the dates so that the data always looks coherent.
     */
    public function definition(): array
    {
        $start = Carbon::instance(fake()->dateTimeBetween('-30 days', '+30 days'));
        $end   = $start->copy()->addDays(fake()->numberBetween(1, 14));

        return $this->period($start, $end) + [
            // Temporary unique value: configure() below replaces it with RNT-2026-0001...
            'reference'      => 'TMP-' . Str::uuid(),
            'user_id'        => $this->randomUserId(),
            'equipment_id'   => $this->randomEquipmentId(),
            // Left empty on purpose: reservations belong to Student 4's module.
            // After the integration, the seeder can link a real reservation here.
            'reservation_id' => null,
        ];
    }

    /** After a rental is saved, give it its final reference (same format as the controller). */
    public function configure(): static
    {
        return $this->afterCreating(function (Rental $rental) {
            $rental->update([
                'reference' => sprintf('RNT-%d-%04d', now()->year, $rental->id),
            ]);
        });
    }

    // ------------------------------------------------------------------
    // States: ready-made variations, e.g. Rental::factory()->active()
    // ------------------------------------------------------------------

    /** Starts in the future: not started yet. */
    public function pending(): static
    {
        return $this->state(function () {
            $start = today()->addDays(fake()->numberBetween(2, 20));
            return $this->period($start, $start->copy()->addDays(fake()->numberBetween(1, 10)));
        });
    }

    /** Started in the past and ends in the future: currently running. */
    public function active(): static
    {
        return $this->state(function () {
            $start = today()->subDays(fake()->numberBetween(1, 5));
            return $this->period($start, today()->addDays(fake()->numberBetween(2, 10)));
        });
    }

    /** Ended in the past. */
    public function completed(): static
    {
        return $this->state(function () {
            $end = today()->subDays(fake()->numberBetween(2, 20));
            return $this->period($end->copy()->subDays(fake()->numberBetween(1, 10)), $end);
        });
    }

    /** Cancelled before starting: same dates as a pending rental, other status. */
    public function cancelled(): static
    {
        return $this->pending()->state(['status' => RentalStatus::CANCELLED->value]);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Builds the dates, the total amount and the status of a rental.
     *  - total = number of days x a random price per day
     *  - status deduced from the dates (future = pending, past = completed, else active)
     */
    private function period(Carbon $start, Carbon $end): array
    {
        $days  = max(1, (int) $start->diffInDays($end));
        $today = today();

        if ($end->lt($today)) {
            $status = RentalStatus::COMPLETED;
        } elseif ($start->gt($today)) {
            $status = RentalStatus::PENDING;
        } else {
            $status = RentalStatus::ACTIVE;
        }

        return [
            'start_date'   => $start->toDateString(),
            'end_date'     => $end->toDateString(),
            'total_amount' => round($days * fake()->randomElement([10, 15, 20, 25, 30, 45, 60]), 2),
            'status'       => $status->value,
        ];
    }

    /** An existing user, picked at random (a new user is created only if there is none). */
    private function randomUserId(): int|Factory
    {
        return User::query()->inRandomOrder()->value('id') ?? User::factory();
    }

    /**
     * An existing equipment id when Student 1's Equipment model and table exist,
     * otherwise a made-up id (there is no foreign key yet, so it is accepted).
     */
    private function randomEquipmentId(): int|Factory
    {
        if (Schema::hasTable('equipment')) {
            $id = Equipment::query()
                ->where('status', 'available')
                ->where('approval_status', 'published')
                ->inRandomOrder()
                ->value('id');

            if ($id) {
                return (int) $id;
            }

            return Equipment::factory();
        }

        return Equipment::factory();
    }
}
