<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the "rental_extensions" table.
     * A rental can have MANY extension requests (relation 1-N with rentals).
     */
    public function up(): void
    {
        Schema::create('rental_extensions', function (Blueprint $table) {
            $table->id();

            // Rental concerned by the request.
            // cascadeOnDelete: deleting a rental deletes its extension requests.
            $table->foreignId('rental_id')->constrained()->cascadeOnDelete();

            // Date when the extension was requested
            $table->date('requested_date');

            // End date of the rental before / after the extension
            $table->date('old_end_date');
            $table->date('new_end_date');

            // Extra money to pay for the additional days
            $table->decimal('additional_amount', 10, 2);

            // Why the renter wants more time (optional)
            $table->text('reason')->nullable();

            // Value comes from App\Enums\ExtensionStatus
            $table->string('status')->default('pending')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_extensions');
    }
};