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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('domain')->nullable()->unique(); // Custom domain
            $table->text('description')->nullable();
            $table->enum('plan', ['free', 'starter', 'professional', 'enterprise'])->default('free');
            $table->enum('status', ['active', 'suspended', 'cancelled', 'trial'])->default('trial');
            $table->json('settings')->nullable(); // features, limits, branding
            $table->json('billing_info')->nullable(); // Stripe customer ID, payment method
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_id', 'status']);
            $table->index(['plan', 'status']);
        });

        Schema::create('organization_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['owner', 'admin', 'manager', 'member', 'viewer'])->default('member');
            $table->json('permissions')->nullable(); // Custom permissions override
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->string('invite_token')->nullable()->unique();
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);
            $table->index(['organization_id', 'role']);
            $table->index(['user_id', 'accepted_at']);
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color')->nullable(); // Hex color for UI
            $table->string('icon')->nullable();
            $table->boolean('is_private')->default(false);
            $table->foreignId('lead_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('settings')->nullable(); // Notifications, permissions, workflows
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'is_private']);
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['lead', 'admin', 'member', 'viewer'])->default('member');
            $table->json('permissions')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
            $table->index(['team_id', 'role']);
        });

        Schema::create('organization_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->enum('role', ['admin', 'manager', 'member', 'viewer'])->default('member');
            $table->json('team_ids')->nullable(); // Pre-assign to teams
            $table->string('token')->unique();
            $table->enum('status', ['pending', 'accepted', 'expired', 'revoked'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'email']);
            $table->index(['token', 'status']);
        });

        Schema::create('team_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->enum('role', ['admin', 'member', 'viewer'])->default('member');
            $table->string('token')->unique();
            $table->enum('status', ['pending', 'accepted', 'expired', 'revoked'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'email']);
            $table->index(['token', 'status']);
        });

        // Add organization_id and team_id to existing tables
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_organization_id')->nullable()->constrained('organizations')->nullOnDelete()->after('profile_photo_path');
            $table->foreignId('current_team_id')->nullable()->constrained('teams')->nullOnDelete()->after('current_organization_id');
            $table->json('organization_preferences')->nullable()->after('notification_preferences');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete()->after('owner_id');
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete()->after('organization_id');
        });

        Schema::table('rentals', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete()->after('user_id');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete()->after('user_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete()->after('reservation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('rentals', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_team_id']);
            $table->dropColumn('current_team_id');
            $table->dropForeign(['current_organization_id']);
            $table->dropColumn('current_organization_id');
            $table->dropColumn('organization_preferences');
        });

        Schema::dropIfExists('team_invitations');
        Schema::dropIfExists('organization_invitations');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('organization_members');
        Schema::dropIfExists('organizations');
    }
};
