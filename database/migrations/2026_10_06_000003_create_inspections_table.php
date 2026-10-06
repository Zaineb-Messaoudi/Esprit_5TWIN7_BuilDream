<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $equipmentExists = Schema::hasTable('equipment');
        $rentalsExist = Schema::hasTable('rentals');

        Schema::create('inspections', function (Blueprint $table) use ($equipmentExists, $rentalsExist) {
            $table->id();
            $table->unsignedBigInteger('equipment_id');
            $table->unsignedBigInteger('rental_id')->nullable()->index();
            $table->date('inspection_date');
            $table->string('condition_before');
            $table->string('condition_after');
            $table->boolean('damage_detected')->default(false);
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->index(['equipment_id', 'inspection_date']);
            if ($equipmentExists) {
                $table->foreign('equipment_id')->references('id')->on('equipment')->restrictOnDelete();
            }
            if ($rentalsExist) {
                $table->foreign('rental_id')->references('id')->on('rentals')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
