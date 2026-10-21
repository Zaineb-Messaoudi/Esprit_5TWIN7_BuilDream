<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->string('image_url', 2048)->nullable();
                $table->string('status', 30)->default('active')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('equipment')) {
            Schema::create('equipment', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('category_id')->constrained()->restrictOnDelete();
                $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
                $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
                $table->string('name');
                $table->string('image_url')->nullable();
                $table->text('description')->nullable();
                $table->string('brand')->nullable();
                $table->string('model')->nullable();
                $table->decimal('price_per_day', 10, 2);
                $table->string('condition', 30)->default('good');
                $table->string('location');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('status', 30)->default('available')->index();
                $table->string('approval_status', 30)->default('published')->index();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['category_id', 'status']);
                $table->index(['owner_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        // Keep restored tables and data; they may have existed before this migration ran.
    }
};
