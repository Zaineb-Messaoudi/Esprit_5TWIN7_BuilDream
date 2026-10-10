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
        Schema::create('admin_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('report_type'); // 'user', 'equipment', 'review', 'listing', 'message', 'payment', 'fraud'
            $table->unsignedBigInteger('subject_id');
            $table->string('subject_type');
            $table->string('reason'); // 'spam', 'inappropriate', 'fraud', 'fake', 'harassment', 'copyright', 'other'
            $table->text('description')->nullable();
            $table->json('evidence')->nullable(); // URLs, screenshots
            $table->enum('status', ['pending', 'under_review', 'resolved', 'dismissed', 'escalated'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['reporter_id', 'status']);
        });

        Schema::create('content_moderation_queue', function (Blueprint $table) {
            $table->id();
            $table->string('content_type'); // 'review', 'listing', 'message', 'profile', 'image', 'document'
            $table->unsignedBigInteger('content_id');
            $table->string('content_hash')->nullable(); // for deduplication
            $table->enum('status', ['pending', 'approved', 'rejected', 'flagged', 'escalated'])->default('pending');
            $table->json('flagged_categories')->nullable(); // ['spam', 'inappropriate', 'profanity', 'pii', 'fake', 'copyright']
            $table->decimal('confidence_score', 3, 2)->nullable(); // AI moderation confidence
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['content_type', 'content_id']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('admin_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('action_type'); // 'user_ban', 'user_unban', 'content_delete', 'content_restore', 'listing_approve', 'listing_reject', 'user_warn', 'refund_issue', 'payout_issue', 'settings_change'
            $table->string('target_type')->nullable(); // 'User', 'Equipment', 'Review', 'Rental', 'User'
            $table->unsignedBigInteger('target_id')->nullable();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'created_at']);
            $table->index(['action_type', 'created_at']);
            $table->index(['target_type', 'target_id']);
        });

        Schema::create('system_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['info', 'warning', 'maintenance', 'feature', 'policy', 'emergency']);
            $table->enum('audience', ['all', 'owners', 'renters', 'admins', 'specific']);
            $table->json('target_users')->nullable(); // specific user IDs
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->boolean('is_published')->default(false);
            $table->boolean('send_email')->default(false);
            $table->boolean('send_push')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['is_published', 'expires_at']);
            $table->index(['audience', 'is_published']);
        });

        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group')->nullable(); // 'general', 'payments', 'moderation', 'notifications', 'security', 'features'
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // 'string', 'integer', 'boolean', 'json', 'float'
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // can be read via API
            $table->boolean('is_editable')->default(true);
            $table->timestamps();

            $table->index(['group', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
        Schema::dropIfExists('system_announcements');
        Schema::dropIfExists('admin_actions');
        Schema::dropIfExists('content_moderation_queue');
        Schema::dropIfExists('admin_reports');
    }
};
