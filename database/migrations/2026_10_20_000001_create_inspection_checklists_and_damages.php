<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inspection_checklists', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('category_name');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('inspection_checklist_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inspection_checklist_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->boolean('is_critical')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('inspections', function (Blueprint $table): void {
            $table->foreignId('inspection_checklist_id')->nullable()->after('rental_id')->constrained()->nullOnDelete();
            $table->json('checklist_results')->nullable()->after('damage_detected');
        });

        Schema::create('damages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('equipment_id')->index();
            $table->foreignId('inspection_checklist_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('severity')->default('severe');
            $table->string('description');
            $table->string('status')->default('reported');
            $table->timestamps();
            $table->unique(['inspection_id', 'inspection_checklist_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damages');
        Schema::table('inspections', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('inspection_checklist_id');
            $table->dropColumn('checklist_results');
        });
        Schema::dropIfExists('inspection_checklist_items');
        Schema::dropIfExists('inspection_checklists');
    }
};
