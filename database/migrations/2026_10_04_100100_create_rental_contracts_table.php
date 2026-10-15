<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the "rental_contracts" table.
     * Each rental has exactly ONE contract (relation 1-1 with rentals).
     */
    public function up(): void
    {
        Schema::create('rental_contracts', function (Blueprint $table) {
            $table->id();

            // Link to the rental. unique() is what makes the relation 1-1:
            // the same rental_id cannot appear twice in this table.
            // cascadeOnDelete: deleting a rental deletes its contract.
            $table->foreignId('rental_id')->unique()->constrained()->cascadeOnDelete();

            // Unique contract code, e.g. "CTR-2026-0001"
            $table->string('contract_number')->unique();

            // Date and time of the signature. Empty (null) until the contract is signed.
            $table->timestamp('signed_at')->nullable();

            // Terms and conditions written in the contract
            $table->text('terms');

            // Security deposit amount (0 if there is no deposit)
            $table->decimal('deposit_amount', 10, 2)->default(0);

            // Value comes from App\Enums\ContractStatus
            $table->string('contract_status')->default('draft')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_contracts');
    }
};
