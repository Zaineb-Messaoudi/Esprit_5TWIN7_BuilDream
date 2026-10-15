<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Analytics report execution run.
 */
class AnalyticsReportRun extends Model
{
    use HasFactory;

    protected $table = 'analytics_report_runs';

    protected $fillable = [
        'report_id',
        'status',
        'parameters',
        'result_summary',
        'file_path',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'result_summary' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** The report this run belongs to. */
    public function report(): BelongsTo
    {
        return $this->belongsTo(AnalyticsReport::class);
    }

    /** Check if run is completed. */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /** Check if run failed. */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /** Mark as started. */
    public function markStarted(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /** Mark as completed. */
    public function markCompleted(array $summary = [], ?string $filePath = null): void
    {
        $this->update([
            'status' => 'completed',
            'result_summary' => $summary,
            'file_path' => $filePath,
            'completed_at' => now(),
        ]);
    }

    /** Mark as failed. */
    public function markFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'completed_at' => now(),
        ]);
    }
}
