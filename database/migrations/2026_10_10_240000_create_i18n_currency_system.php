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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // ISO 4217 (USD, EUR, TND, etc.)
            $table->string('name');
            $table->string('symbol'); // $, €, ت.د, etc.
            $table->string('symbol_native')->nullable(); // Native script symbol
            $table->integer('decimal_places')->default(2);
            $table->decimal('exchange_rate', 20, 8)->default(1); // Relative to base currency
            $table->boolean('is_base')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_crypto')->default(false);
            $table->json('metadata')->nullable(); // flags, regions, etc.
            $table->timestamps();

            $table->index(['is_active', 'is_base']);
        });

        Schema::create('currency_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignId('to_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('rate', 20, 8);
            $table->date('date');
            $table->string('source')->default('manual'); // fixer, exchangerate, manual
            $table->timestamps();

            $table->unique(['from_currency_id', 'to_currency_id', 'date'], 'curr_exch_rate_unique');
            $table->index(['from_currency_id', 'to_currency_id', 'date'], 'curr_exch_rate_idx');
        });

        Schema::create('language_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10); // en, fr, ar, etc.
            $table->boolean('is_rtl')->default(false);
            $table->string('currency_code', 3)->nullable();
            $table->string('timezone')->nullable();
            $table->json('date_format')->nullable(); // {format: 'd/m/Y', locale: 'ar'}
            $table->json('number_format')->nullable(); // {decimal: ',', thousands: '.'}
            $table->timestamps();

            $table->unique(['user_id']);
        });

        Schema::create('translation_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // 'auth.login', 'equipment.rent_now'
            $table->text('description')->nullable(); // Context for translators
            $table->string('group')->nullable(); // 'auth', 'equipment', 'dashboard'
            $table->timestamps();

            $table->index(['group']);
        });

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_key_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->longText('value');
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['translation_key_id', 'locale']);
            $table->index(['locale']);
        });

        // Add locale and currency to organizations
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('default_locale', 10)->default('en')->after('settings');
            $table->string('default_currency_code', 3)->default('TND')->after('default_locale');
            $table->boolean('rtl_enabled')->default(false)->after('default_currency_code');
            $table->json('supported_locales')->nullable()->after('rtl_enabled');
        });

        // Add locale and currency to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 10)->default('en')->after('organization_preferences');
            $table->string('currency_code', 3)->nullable()->after('locale');
            $table->boolean('rtl_preference')->default(false)->after('currency_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rtl_preference', 'currency_code', 'locale']);
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['supported_locales', 'rtl_enabled', 'default_currency_code', 'default_locale']);
        });

        Schema::dropIfExists('translations');
        Schema::dropIfExists('translation_keys');
        Schema::dropIfExists('language_preferences');
        Schema::dropIfExists('currency_exchange_rates');
        Schema::dropIfExists('currencies');
    }
};
