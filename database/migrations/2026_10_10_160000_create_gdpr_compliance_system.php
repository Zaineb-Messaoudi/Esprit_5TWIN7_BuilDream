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
        Schema::create('consent_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('consent_type'); // 'marketing', 'analytics', 'functional', 'third_party', 'cookies', 'data_processing'
            $table->boolean('granted');
            $table->string('version')->nullable();
            $table->text('consent_text')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'consent_type']);
        });

        Schema::create('data_export_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'processing', 'ready', 'downloaded', 'expired', 'cancelled'])->default('pending');
            $table->json('include_categories')->nullable(); // ['profile', 'rentals', 'payments', 'reviews', 'messages', 'notifications', 'all']
            $table->json('exclude_categories')->nullable();
            $table->date('date_range_start')->nullable();
            $table->date('date_range_end')->nullable();
            $table->string('format')->default('json'); // 'json', 'csv', 'xml', 'pdf'
            $table->string('file_path')->nullable();
            $table->integer('file_size')->nullable();
            $table->integer('record_count')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('data_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'verification_sent', 'verified', 'processing', 'completed', 'rejected', 'cancelled'])->default('pending');
            $table->text('reason')->nullable();
            $table->json('delete_categories')->nullable(); // ['profile', 'rentals', 'payments', 'reviews', 'messages', 'all']
            $table->boolean('anonymize_instead')->default(false);
            $table->string('verification_token')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type'); // 'login', 'logout', 'password_change', 'data_export', 'data_deletion', 'consent_change', 'profile_update', 'payment', 'admin_action'
            $table->string('event_action'); // 'create', 'read', 'update', 'delete', 'export', 'import'
            $table->string('subject_type')->nullable(); // 'User', 'Rental', 'Payment', 'Equipment'
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'event_type', 'created_at']);
            $table->index(['event_type', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('data_retention_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category'); // 'user_data', 'rental_data', 'payment_data', 'logs', 'communications'
            $table->integer('retention_days');
            $table->enum('action_on_expiry', ['anonymize', 'delete', 'archive']);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_retention_policies');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('data_deletion_requests');
        Schema::dropIfExists('data_export_requests');
        Schema::dropIfExists('consent_records');
    }
};
