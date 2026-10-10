<?php

namespace App\Http\Controllers\Api;

use App\Models\AnalyticsDashboard;
use App\Models\AnalyticsReport;
use App\Models\AnalyticsWidget;
use App\Models\PredictiveModel;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Analytics dashboard and reporting API controller.
 */
class AnalyticsController
{
    public function __construct(private readonly \App\Services\AnalyticsService $service) {}

    /**
     * Get revenue analytics.
     */
    public function revenue(Request $request): JsonResponse
    {
        $start = $request->get('start_date') ? \Carbon\Carbon::parse($request->get('start_date')) : null;
        $end = $request->get('end_date') ? \Carbon\Carbon::parse($request->get('end_date')) : null;

        $analytics = $this->service->getRevenueAnalytics($start, $end);

        return response()->json($analytics);
    }

    /**
     * Get booking analytics.
     */
    public function bookings(Request $request): JsonResponse
    {
        $start = $request->get('start_date') ? \Carbon\Carbon::parse($request->get('start_date')) : null;
        $end = $request->get('end_date') ? \Carbon\Carbon::parse($request->get('end_date')) : null;

        $analytics = $this->service->getBookingAnalytics($start, $end);

        return response()->json($analytics);
    }

    /**
     * Get equipment utilization analytics.
     */
    public function utilization(Request $request): JsonResponse
    {
        $start = $request->get('start_date') ? \Carbon\Carbon::parse($request->get('start_date')) : null;
        $end = $request->get('end_date') ? \Carbon\Carbon::parse($request->get('end_date')) : null;

        $analytics = $this->service->getUtilizationAnalytics($start, $end);

        return response()->json($analytics);
    }

    /**
     * Get user growth analytics.
     */
    public function userGrowth(Request $request): JsonResponse
    {
        $start = $request->get('start_date') ? \Carbon\Carbon::parse($request->get('start_date')) : null;
        $end = $request->get('end_date') ? \Carbon\Carbon::parse($request->get('end_date')) : null;

        $analytics = $this->service->getUserGrowthAnalytics($start, $end);

        return response()->json($analytics);
    }

    /**
     * Get top performing equipment.
     */
    public function topEquipment(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 10);
        $start = $request->get('start_date') ? \Carbon\Carbon::parse($request->get('start_date')) : null;
        $end = $request->get('end_date') ? \Carbon\Carbon::parse($request->get('end_date')) : null;

        $equipment = $this->service->getTopEquipment($limit, $start, $end);

        return response()->json($equipment);
    }

    /**
     * Record a custom metric event.
     */
    public function recordEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:50'],
            'value' => ['nullable', 'numeric'],
            'dimensions' => ['nullable', 'array'],
            'metric_type' => ['nullable', 'in:counter,gauge,histogram,rate'],
        );

        $this->service->recordEvent(
            $validated['event_name'],
            $validated['category'],
            $validated['dimensions'] ?? [],
            $validated['value'] ?? 1,
            $validated['metric_type'] ?? 'counter'
        );

        return response()->json(['message' => 'Event recorded.']);
    }

    /**
     * List dashboards.
     */
    public function dashboards(Request $request): JsonResponse
    {
        $dashboards = AnalyticsDashboard::forUser($request->user())
            ->with('widgets')
            ->latest()
            ->paginate(20);

        return response()->json($dashboards);
    }

    /**
     * Create a dashboard.
     */
    public function storeDashboard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'visibility' => ['required', 'in:private,team,organization,public'],
            'layout' => ['nullable', 'array'],
            'widgets' => ['nullable', 'array'],
            'is_default' => ['boolean'],
        );

        $dashboard = AnalyticsDashboard::create([
            ...$validated,
            'owner_id' => $request->user()->id,
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
        ]);

        if (!empty($validated['widgets'])) {
            foreach ($validated['widgets'] as $index => $widget) {
                AnalyticsWidget::create([
                    'dashboard_id' => $dashboard->id,
                    'title' => $widget['title'],
                    'type' => $widget['type'],
                    'config' => $widget['config'] ?? [],
                    'position' => $widget['position'] ?? ['x' => 0, 'y' => 0, 'w' => 4, 'h' => 3],
                    'sort_order' => $index,
                    'is_visible' => $widget['is_visible'] ?? true,
                ]);
            }
        }

        return response()->json([
            'message' => 'Dashboard created.',
            'dashboard' => $dashboard->load('widgets'),
        ], 201);
    }

    /**
     * Get a single dashboard.
     */
    public function showDashboard(AnalyticsDashboard $dashboard): JsonResponse
    {
        abort_unless($dashboard->isVisibleTo(request()->user()), 403);

        return response()->json($dashboard->load('widgets'));
    }

    /**
     * Update a dashboard.
     */
    public function updateDashboard(Request $request, AnalyticsDashboard $dashboard): JsonResponse
    {
        abort_unless($dashboard->owner_id === request()->user()->id, 403);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'visibility' => ['nullable', 'in:private,team,organization,public'],
            'layout' => ['nullable', 'array'],
            'is_default' => ['boolean'],
        ]);

        $dashboard->update($validated);

        return response()->json([
            'message' => 'Dashboard updated.',
            'dashboard' => $dashboard->load('widgets'),
        ]);
    }

    /**
     * Delete a dashboard.
     */
    public function destroyDashboard(AnalyticsDashboard $dashboard): JsonResponse
    {
        abort_unless($dashboard->owner_id === request()->user()->id, 403);

        $dashboard->delete();

        return response()->json(['message' => 'Dashboard deleted.']);
    }

    /**
     * Add widget to dashboard.
     */
    public function addWidget(Request $request, AnalyticsDashboard $dashboard): JsonResponse
    {
        abort_unless($dashboard->owner_id === request()->user()->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:metric,line,bar,area,pie,donut,funnel,heatmap,geo,table,gauge'],
            'config' => ['required', 'array'],
            'position' => ['nullable', 'array'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $widget = AnalyticsWidget::create([
            'dashboard_id' => $dashboard->id,
            ...$validated,
        ]);

        return response()->json([
            'message' => 'Widget added.',
            'widget' => $widget,
        ], 201);
    }

    /**
     * Update a widget.
     */
    public function updateWidget(Request $request, AnalyticsWidget $widget): JsonResponse
    {
        abort_unless($widget->dashboard->owner_id === request()->user()->id, 403);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'in:metric,line,bar,area,pie,donut,funnel,heatmap,geo,table,gauge'],
            'config' => ['nullable', 'array'],
            'position' => ['nullable', 'array'],
            'sort_order' => ['nullable', 'integer'],
            'is_visible' => ['nullable', 'boolean'],
        ]);

        $widget->update($validated);

        return response()->json([
            'message' => 'Widget updated.',
            'widget' => $widget->fresh(),
        ]);
    }

    /**
     * Delete a widget.
     */
    public function destroyWidget(AnalyticsWidget $widget): JsonResponse
    {
        abort_unless($widget->dashboard->owner_id === request()->user()->id, 403);

        $widget->delete();

        return response()->json(['message' => 'Widget deleted.']);
    }

    /**
     * List reports.
     */
    public function reports(Request $request): JsonResponse
    {
        $reports = AnalyticsReport::active()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(20);

        return response()->json($reports);
    }

    /**
     * Create a report.
     */
    public function storeReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:revenue,utilization,booking,user_growth,equipment_performance,financial,custom'],
            'configuration' => ['required', 'array'],
            'schedule' => ['required', 'in:manual,daily,weekly,monthly'],
            'schedule_config' => ['nullable', 'array'],
            'format' => ['required', 'in:json,csv,pdf,excel'],
        );

        $report = AnalyticsReport::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
        ]);

        return response()->json([
            'message' => 'Report created.',
            'report' => $report,
        ], 201);
    }

    /**
     * Get a single report.
     */
    public function showReport(AnalyticsReport $report): JsonResponse
    {
        return response()->json($report->load('runs'));
    }

    /**
     * Generate a report.
     */
    public function generateReport(Request $request, AnalyticsReport $report): JsonResponse
    {
        $run = AnalyticsReportRun::create([
            'report_id' => $report->id,
            'status' => 'pending',
            'parameters' => $request->input('parameters', []),
        ]);

        // TODO: Queue report generation job
        // GenerateReportJob::dispatch($run, $request->input('parameters', []));

        return response()->json([
            'message' => 'Report generation queued.',
            'run' => $run,
        ], 202);
    }

    /**
     * Get report run status.
     */
    public function showReportRun(AnalyticsReportRun $run): JsonResponse
    {
        return response()->json($run->load('report'));
    }

    /**
     * List predictive models.
     */
    public function models(Request $request): JsonResponse
    {
        $models = PredictiveModel::with('creator:id,name')
            ->when($request->filled('type'), fn ($q) => $q->type($request->type))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return response()->json($models);
    }

    /**
     * Get model predictions.
     */
    public function modelPredictions(Request $request, PredictiveModel $model): JsonResponse
    {
        $predictions = $model->predictions()
            ->when($request->filled('with_actual'), fn ($q) => $q->whereNotNull('actual->value'))
            ->latest()
            ->paginate(20);

        return response()->json($predictions);
    }

    /**
     * Make a prediction.
     */
    public function predict(Request $request, PredictiveModel $model): JsonResponse
    {
        abort_unless($model->isReady(), 400, 'Model is not ready for predictions.');

        $validated = $request->validate([
            'input_features' => ['required', 'array'],
        );

        // TODO: Call ML model service
        // $prediction = $this->mlService->predict($model, $validated['input_features']);

        // For now, return mock prediction
        $prediction = Prediction::create([
            'model_id' => $model->id,
            'input_features' => $validated['input_features'],
            'prediction' => [
                'value' => 0, // Would come from ML model
                'confidence_interval' => [0, 0],
            ],
            'predicted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Prediction generated.',
            'prediction' => $prediction,
        ]);
    }
}