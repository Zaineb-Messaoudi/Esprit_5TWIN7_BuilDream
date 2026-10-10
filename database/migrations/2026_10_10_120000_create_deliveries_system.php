<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('renter_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['pickup', 'dropoff', 'both']);
            $table->enum('status', ['scheduled', 'in_transit', 'arrived', 'completed', 'cancelled', 'issue']);
            $table->string('tracking_number')->unique()->nullable();
            $table->string('carrier')->nullable(); // 'self', 'dhl', 'ups', 'fedex', 'local', etc.
            $table->json('pickup_address'); // {street, city, postal_code, country, lat, lng}
            $table->json('dropoff_address');
            $table->timestamp('scheduled_pickup_at')->nullable();
            $table->timestamp('actual_pickup_at')->nullable();
            $table->timestamp('scheduled_dropoff_at')->nullable();
            $table->timestamp('actual_dropoff_at')->nullable();
            $table->json('dimensions')->nullable(); // {length, width, height, weight}
            $table->text('special_instructions')->nullable();
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->enum('fee_paid_by', ['owner', 'renter', 'split'])->default('split');
            $table->string('proof_of_pickup_url')->nullable();
            $table->string('proof_of_dropoff_url')->nullable();
            $table->text('issue_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['rental_id', 'type']);
            $table->index(['owner_id', 'status']);
            $table->index(['renter_id', 'status']);
            $table->index(['status', 'scheduled_pickup_at']);
        });

        Schema::create('delivery_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['scheduled', 'in_transit', 'arrived', 'completed', 'cancelled', 'issue']);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['delivery_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_updates');
        Schema::dropIfExists('deliveries');
    }
};
