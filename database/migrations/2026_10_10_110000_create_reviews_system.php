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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewee_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['owner_to_renter', 'renter_to_owner']);
            $table->tinyInteger('overall_rating')->unsigned(); // 1-5
            $table->tinyInteger('communication_rating')->unsigned()->nullable();
            $table->tinyInteger('reliability_rating')->unsigned()->nullable();
            $table->tinyInteger('condition_rating')->unsigned()->nullable();
            $table->tinyInteger('value_rating')->unsigned()->nullable();
            $table->text('comment')->nullable();
            $table->json('photos')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['rental_id', 'reviewer_id']);
            $table->index(['reviewee_id', 'is_public']);
        });

        Schema::create('review_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->foreignId('initiator_id')->constrained('users')->cascadeOnDelete();
            $table->enum('reason', ['inaccurate', 'harassment', 'fake', 'irrelevant', 'other']);
            $table->text('description');
            $table->enum('status', ['open', 'under_review', 'resolved', 'dismissed'])->default('open');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('review_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('response');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_responses');
        Schema::dropIfExists('review_disputes');
        Schema::dropIfExists('reviews');
    }
};
