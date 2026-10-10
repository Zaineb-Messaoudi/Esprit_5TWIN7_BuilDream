<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Analytics report definition.
 */
class AnalyticsReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'analytics_reports';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'configuration',
        'schedule',
        'schedule_config',
        'format',
        'is_active',
        'created_by',
        'last_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'configuration' => 'array',
            'schedule_config' => 'array',
            'is_active' => 'boolean',
            'last_generated_at' => 'datetime',
        ];
    }

    /** The user who created the report. */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Report execution runs. */
    public function runs(): HasMany
    {
        return $this->hasMany(AnalyticsReportRun::class)->latest();
    }

    /** Get the latest run. */
    public function latestRun()
    {
        return $this->runs()->latest()->first();
    }

    /** Scope for active reports. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Scope for scheduled reports. */
    public function scopeScheduled($query)
    {
        return $query->where('schedule', '!=', 'manual');
    }
}
