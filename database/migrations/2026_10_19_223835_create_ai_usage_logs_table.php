<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint'); // natural-language-search, chatbot, etc.
            $table->string('provider'); // ollama, openai, anthropic
            $table->string('model')->nullable(); // llama3.2, gpt-4o-mini, etc.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('request_id')->nullable(); // X-Request-ID for tracing
            $table->json('request_payload')->nullable(); // Sanitized request
            $table->json('response_payload')->nullable(); // Sanitized response
            $table->unsignedInteger('latency_ms'); // Total latency
            $table->unsignedInteger('tokens_input')->default(0);
            $table->unsignedInteger('tokens_output')->default(0);
            $table->unsignedInteger('tokens_total')->default(0);
            $table->decimal('cost_usd', 10, 6)->default(0); // For paid providers
            $table->unsignedInteger('gpu_time_ms')->nullable(); // For Ollama
            $table->unsignedInteger('vram_used_mb')->nullable(); // For Ollama
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->string('prompt_hash', 64)->nullable(); // Links to ai_prompts
            $table->timestamps();

            $table->index(['endpoint', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['provider', 'model', 'created_at']);
            $table->index(['request_id']);
            $table->index(['success', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};