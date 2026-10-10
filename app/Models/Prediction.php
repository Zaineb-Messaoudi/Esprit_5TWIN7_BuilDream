<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ML model prediction.
 */
class Prediction extends Model
{
    use HasFactory;

    protected $table = 'predictions';

    protected $fillable = [
        'model_id',
        'input_features',
        'prediction',
        'actual',
        'accuracy_score',
        'predicted_at',
        'actual_at',
    ];

    protected function casts(): array
    {
        return [
            'input_features' => 'array',
            'prediction' => 'array',
            'actual' => 'array',
            'accuracy_score' => 'decimal:4',
            'predicted_at' => 'datetime',
            'actual_at' => 'datetime',
        ];
    }

    /** The model that made this prediction. */
    public function model(): BelongsTo
    {
        return $this->belongsTo(PredictiveModel::class);
    }

    /** Get predicted value. */
    public function getPredictedValue(): ?float
    {
        return $this->prediction['value'] ?? null;
    }

    /** Get confidence interval. */
    public function getConfidenceInterval(): ?array
    {
        return $this->prediction['confidence_interval'] ?? null;
    }

    /** Get actual value. */
    public function getActualValue(): ?float
    {
        return $this->actual['value'] ?? null;
    }

    /** Check if prediction has actual value for accuracy calculation. */
    public function hasActual(): bool
    {
        return ! is_null($this->actual['value'] ?? null);
    }

    /** Calculate accuracy if actual is available. */
    public function calculateAccuracy(): ?float
    {
        if (! $this->hasActual()) {
            return null;
        }

        $predicted = $this->getPredictedValue();
        $actual = $this->getActualValue();

        if ($predicted === null || $actual === null || $actual === 0) {
            return null;
        }

        // Mean Absolute Percentage Error
        $mape = abs(($actual - $predicted) / $actual) * 100;
        $accuracy = max(0, 100 - $mape);

        $this->update(['accuracy_score' => round($accuracy, 2)]);

        return $this->accuracy_score;
    }

    /** Set actual value and calculate accuracy. */
    public function setActual(float $value, ?\Illuminate\Support\Carbon $at = null): void
    {
        $this->update([
            'actual' => ['value' => $value],
            'actual_at' => $at ?? now(),
        ]);

        $this->calculateAccuracy();
    }

    /** Scope for predictions with actual values. */
    public function scopeWithActual($query)
    {
        return $query->whereNotNull('actual->value');
    }

    /** Scope for recent predictions. */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('predicted_at', '>=', now()->subDays($days));
    }
}
