<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_assistant_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('service_type');
            $table->text('custom_description')->nullable();
            $table->decimal('surface_m2', 10, 2)->nullable();
            $table->string('urgency');
            $table->decimal('budget', 10, 2);
            $table->string('address');
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_assistant_requests');
    }
};
