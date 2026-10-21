<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('code', 3)->unique();
                $table->string('name');
                $table->string('symbol');
                $table->string('symbol_native')->nullable();
                $table->integer('decimal_places')->default(2);
                $table->decimal('exchange_rate', 20, 8)->default(1);
                $table->boolean('is_base')->default(false);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_crypto')->default(false);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['is_active', 'is_base']);
            });

            DB::table('currencies')->insert([
                'code' => 'TND',
                'name' => 'Tunisian Dinar',
                'symbol' => 'TND',
                'symbol_native' => 'د.ت',
                'decimal_places' => 3,
                'exchange_rate' => 1,
                'is_base' => true,
                'is_active' => true,
                'is_crypto' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! Schema::hasTable('currency_exchange_rates')) {
            Schema::create('currency_exchange_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('from_currency_id')->constrained('currencies')->cascadeOnDelete();
                $table->foreignId('to_currency_id')->constrained('currencies')->cascadeOnDelete();
                $table->decimal('rate', 20, 8);
                $table->date('date');
                $table->string('source')->default('manual');
                $table->timestamps();
                $table->unique(['from_currency_id', 'to_currency_id', 'date'], 'curr_exch_rate_unique');
                $table->index(['from_currency_id', 'to_currency_id', 'date'], 'curr_exch_rate_idx');
            });
        }
    }

    public function down(): void
    {
        // Keep recovered tables and data; they may have existed before this migration ran.
    }
};
