<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->unique()->constrained('maintenances')->cascadeOnDelete();
            $table->text('diagnosis');
            $table->text('actions_taken');
            $table->text('parts_replaced')->nullable();
            $table->text('technician_notes')->nullable();
            $table->date('report_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_reports');
    }
};
