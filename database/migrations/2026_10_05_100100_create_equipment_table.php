<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // An earlier Student 4 migration may already have created a minimal
        // equipment table. Upgrade it in place instead of trying to recreate it.
        if (Schema::hasTable('equipment')) {
            Schema::table('equipment', function (Blueprint $table): void {
                if (! Schema::hasColumn('equipment', 'category_id')) {
                    $table->foreignId('category_id')->nullable()->after('id');
                }
                if (! Schema::hasColumn('equipment', 'owner_id')) {
                    $table->foreignId('owner_id')->nullable()->after('category_id');
                }
                if (! Schema::hasColumn('equipment', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }
                if (! Schema::hasColumn('equipment', 'brand')) {
                    $table->string('brand')->nullable()->after('description');
                }
                if (! Schema::hasColumn('equipment', 'model')) {
                    $table->string('model')->nullable()->after('brand');
                }
                if (! Schema::hasColumn('equipment', 'price_per_day')) {
                    $table->decimal('price_per_day', 10, 2)->nullable()->after('model');
                }
                if (! Schema::hasColumn('equipment', 'condition')) {
                    $table->string('condition', 30)->default('good')->after('price_per_day');
                }
                if (! Schema::hasColumn('equipment', 'location')) {
                    $table->string('location')->nullable()->after('condition');
                }
                if (! Schema::hasColumn('equipment', 'status')) {
                    $table->string('status', 30)->default('available')->after('location');
                }
            });

            Schema::table('equipment', function (Blueprint $table): void {
                $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
                $table->foreign('owner_id')->references('id')->on('users')->restrictOnDelete();
                $table->index(['category_id', 'status']);
                $table->index(['owner_id', 'status']);
            });

            return;
        }

        Schema::create('equipment', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->decimal('price_per_day', 10, 2);
            $table->string('condition', 30)->default('good');
            $table->string('location');
            $table->string('status', 30)->default('available')->index();
            $table->timestamps();
            $table->index(['category_id', 'status']);
            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
