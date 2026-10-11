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
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('category', ['rental_contract', 'equipment_agreement', 'insurance_policy', 'terms_of_service', 'privacy_policy', 'ndas', 'other']);
            $table->longText('content');
            $table->json('variables')->nullable();
            $table->json('signature_fields')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('document_number')->unique();
            $table->string('title');
            $table->enum('type', ['contract', 'agreement', 'policy', 'invoice', 'certificate', 'report', 'other']);
            $table->enum('status', ['draft', 'pending_signatures', 'partially_signed', 'completed', 'expired', 'cancelled', 'archived'])->default('draft');
            $table->longText('content');
            $table->json('variables')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['owner_id', 'status']);

            $table->foreign('template_id')
                ->references('id')
                ->on('document_templates')
                ->nullOnDelete();
        });

        Schema::create('document_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('signer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('signer_email');
            $table->string('signer_name');
            $table->enum('signer_role', ['owner', 'renter', 'witness', 'admin', 'external'])->default('external');
            $table->enum('status', ['pending', 'viewed', 'signed', 'declined', 'expired'])->default('pending');
            $table->string('signature_data')->nullable();
            $table->string('signature_hash')->nullable();
            $table->json('signature_metadata')->nullable();
            $table->text('decline_reason')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('token')->nullable()->unique();
            $table->timestamps();

            $table->index(['document_id', 'signer_id']);
            $table->index(['signer_email', 'status']);
            $table->index(['token']);
        });

        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->integer('version');
            $table->longText('content');
            $table->json('variables')->nullable();
            $table->text('change_summary')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['document_id', 'version']);
        });

        Schema::create('document_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('document_folders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'parent_id']);
        });

        Schema::create('document_folder_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained('document_folders')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['folder_id', 'document_id']);
        });

        Schema::create('document_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shared_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shared_with')->nullable()->constrained('users')->nullOnDelete();
            $table->string('shared_email')->nullable();
            $table->enum('permission', ['view', 'comment', 'edit', 'sign'])->default('view');
            $table->timestamp('expires_at')->nullable();
            $table->string('token')->unique();
            $table->timestamp('accessed_at')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'shared_with']);
            $table->index(['token']);
        });

        Schema::create('document_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->json('details')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_audit_trails');
        Schema::dropIfExists('document_shares');
        Schema::dropIfExists('document_folder_items');
        Schema::dropIfExists('document_folders');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('document_signatures');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_templates');
    }
};
