<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Analytics metric data point.
 */
class AnalyticsMetric extends Model
{
    use HasFactory;

    protected $table = 'analytics_metrics';

    protected $fillable = [
        'metric_name',
        'metric_type',
        'category',
        'value',
        'dimensions',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
            'dimensions' => 'array',
            'recorded_at' => 'datetime',
        ];
    }

    /** Scope for specific metric. */
    public function scopeMetric($query, string $metricName)
    {
        return $query->where('metric_name', $metricName);
    }

    /** Scope for category. */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /** Scope for date range. */
    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('recorded_at', [$start, $end]);
    }

    /** Scope for last N days. */
    public function scopeLastDays($query, int $days)
    {
        return $query->where('recorded_at', '>=', now()->subDays($days));
    }

    /** Record a metric value. */
    public static function record(
        string $metricName,
        string $category,
        float $value,
        array $dimensions = [],
        string $metricType = 'gauge',
        ?\Illuminate\Support\Carbon $recordedAt = null
    ): self {
        return static::create([
            'metric_name' => $metricName,
            'metric_type' => $metricType,
            'category' => $category,
            'value' => $value,
            'dimensions' => $dimensions,
            'recorded_at' => $recordedAt ?? now(),
        ]);
    }

    /** Increment a counter metric. */
    public static function incrementCounter(
        string $metricName,
        string $category,
        float $increment = 1,
        array $dimensions = [],
        ?\Illuminate\Support\Carbon $recordedAt = null
    ): self {
        return static::record($metricName, $category, $increment, $dimensions, 'counter', $recordedAt);
    }
}
