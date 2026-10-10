<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Dashboard widget.
 */
class AnalyticsWidget extends Model
{
    use HasFactory;

    protected $table = 'analytics_widgets';

    protected $fillable = [
        'dashboard_id',
        'title',
        'type',
        'config',
        'position',
        'sort_order',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'position' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    /** The dashboard this widget belongs to. */
    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(AnalyticsDashboard::class);
    }

    /** Check if widget is a chart. */
    public function isChart(): bool
    {
        return in_array($this->type, ['line', 'bar', 'area', 'pie', 'donut', 'funnel', 'heatmap']);
    }

    /** Check if widget is a metric display. */
    public function isMetric(): bool
    {
        return $this->type === 'metric';
    }
}
