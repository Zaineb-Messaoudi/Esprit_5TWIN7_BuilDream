<?php

namespace Database\Seeders;

use App\Enums\RentalStatus;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\RentalContract;
use App\Models\RentalExtension;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Fills the rental module with coherent demo data:
 *   12 rentals (3 pending, 4 active, 4 completed, 1 cancelled)
 *   -> 1 contract per rental, matching the rental status
 *   -> a few extension requests (pending / approved / rejected)
 *
 * Run it alone:       php artisan db:seed --class=RentalSeeder
 * Or with everything: php artisan migrate:fresh --seed   (DatabaseSeeder calls this seeder)
 */
class RentalSeeder extends Seeder
{
    public function run(): void
    {
        // Rentals need renters. DatabaseSeeder already creates users, this is just a safety net.
        if (User::count() === 0) {
            User::factory(5)->create();
        }

        // Rental records must always point to real catalogue listings.
        if (Equipment::count() === 0) {
            Equipment::factory()->count(3)->create();
        }

        // Keep the named demo buyer's workspace backed by real rental rows.
        // Other users can still receive rentals when this account is not present.
        $demoBuyer = User::query()->where('email', 'user@solarshare.com')->first();
        $demoBuyerState = $demoBuyer ? ['user_id' => $demoBuyer->id] : [];

        // ---- 1) Rentals, grouped by status ----------------------------------
        $pending   = Rental::factory()->count(3)->state($demoBuyerState)->pending()->create();
        $active    = Rental::factory()->count(4)->state($demoBuyerState)->active()->create();
        $completed = Rental::factory()->count(4)->state($demoBuyerState)->completed()->create();
        $cancelled = Rental::factory()->count(1)->state($demoBuyerState)->cancelled()->create();

        // ---- 2) One contract per rental (relation 1-1) ----------------------
        foreach ($pending->concat($active)->concat($completed)->concat($cancelled) as $rental) {
            // state(['rental_id' => ...]) links the contract to THIS rental
            // (otherwise the factory would create a brand new rental)
            $contract = RentalContract::factory()->state(['rental_id' => $rental->id]);

            // The contract status follows the rental status
            $contract = match ($rental->status) {
                RentalStatus::PENDING => $contract->draft(),   // not started: still a draft
                RentalStatus::ACTIVE  => $contract->signed(),  // running: signed
                default               => $contract->terminated(), // completed or cancelled: over
            };

            $contract->create();
        }

        // ---- 3) Extension requests (only for rentals that can still be extended) ----

        // Active rental #1: one APPROVED extension, then a new PENDING one on top of it
        $this->createApprovedExtension($active->get(0));
        RentalExtension::factory()->create(['rental_id' => $active->get(0)->id]);

        // Active rental #2: a PENDING request, waiting for a decision
        RentalExtension::factory()->create(['rental_id' => $active->get(1)->id]);

        // Active rental #3: a REJECTED request (the rental stays unchanged)
        RentalExtension::factory()->rejected()->create(['rental_id' => $active->get(2)->id]);

        // Pending rental #1: a PENDING request
        RentalExtension::factory()->create(['rental_id' => $pending->get(0)->id]);
    }

    /**
     * Creates an APPROVED extension AND applies it to the rental, exactly like
     * RentalExtensionController::approve() does: new end date + extra amount.
     */
    private function createApprovedExtension(Rental $rental): void
    {
        $extension = RentalExtension::factory()->approved()->create(['rental_id' => $rental->id]);

        $rental->update([
            'end_date'     => $extension->new_end_date,
            'total_amount' => round((float) $rental->total_amount + (float) $extension->additional_amount, 2),
        ]);
    }
}
