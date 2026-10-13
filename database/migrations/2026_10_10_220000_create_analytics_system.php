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
        Schema::create('analytics_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('metric_name');
            $table->string('metric_type'); // 'counter', 'gauge', 'histogram', 'rate'
            $table->string('category'); // 'revenue', 'utilization', 'bookings', 'users', 'equipment', 'platform'
            $table->decimal('value', 20, 4);
            $table->json('dimensions')->nullable(); // {equipment_id, owner_id, category_id, region, etc.}
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['metric_name', 'recorded_at']);
            $table->index(['category', 'recorded_at']);
            $table->index(['metric_name', 'category', 'recorded_at']);
        });

        Schema::create('analytics_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['revenue', 'utilization', 'booking', 'user_growth', 'equipment_performance', 'financial', 'custom']);
            $table->json('configuration'); // Query config, filters, date ranges, visualizations
            $table->enum('schedule', ['manual', 'daily', 'weekly', 'monthly'])->default('manual');
            $table->json('schedule_config')->nullable(); // cron, timezone, recipients
            $table->enum('format', ['json', 'csv', 'pdf', 'excel'])->default('json');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });

        Schema::create('analytics_report_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('analytics_reports')->cascadeOnDelete();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('parameters')->nullable(); // Runtime parameters
            $table->json('result_summary')->nullable(); // Key metrics summary
            $table->string('file_path')->nullable(); // Generated file path
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['report_id', 'status']);
            $table->index(['created_at']);
        });

        Schema::create('analytics_dashboards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('visibility', ['private', 'team', 'organization', 'public'])->default('private');
            $table->json('layout')->nullable(); // Grid layout config
            $table->json('widgets')->nullable(); // Array of widget configs
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['owner_id', 'visibility']);
        });

        Schema::create('analytics_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained('analytics_dashboards')->cascadeOnDelete();
            $table->string('title');
            $table->string('type'); // 'metric', 'chart', 'table', 'gauge', 'funnel', 'heatmap', 'geo'
            $table->json('config'); // Query, visualization config, filters
            $table->json('position'); // {x, y, w, h}
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['dashboard_id', 'sort_order']);
        });

        Schema::create('predictive_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['demand_forecast', 'price_optimization', 'churn_prediction', 'revenue_forecast', 'utilization_forecast', 'maintenance_prediction']);
            $table->enum('status', ['training', 'ready', 'deprecated', 'failed'])->default('training');
            $table->json('features')->nullable(); // Input features
            $table->json('target')->nullable(); // Target variable
            $table->json('hyperparameters')->nullable();
            $table->json('metrics')->nullable(); // MAE, RMSE, R2, etc.
            $table->string('model_path')->nullable(); // Path to serialized model
            $table->json('feature_importance')->nullable();
            $table->timestamp('last_trained_at')->nullable();
            $table->timestamp('deployed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['type', 'status']);
        });

        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('predictive_models')->cascadeOnDelete();
            $table->json('input_features');
            $table->json('prediction'); // {value, confidence_interval, percentile}
            $table->json('actual')->nullable(); // For accuracy tracking
            $table->decimal('accuracy_score')->nullable(); // When actual is known
            $table->timestamp('predicted_at');
            $table->timestamp('actual_at')->nullable();
            $table->timestamps();

            $table->index(['model_id', 'predicted_at']);
            $table->index(['model_id', 'accuracy_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
        Schema::dropIfExists('predictive_models');
        Schema::dropIfExists('analytics_widgets');
        Schema::dropIfExists('analytics_dashboards');
        Schema::dropIfExists('analytics_report_runs');
        Schema::dropIfExists('analytics_reports');
        Schema::dropIfExists('analytics_metrics');
    }
};
