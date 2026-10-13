<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Predictive ML model.
 */
class PredictiveModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'predictive_models';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'status',
        'features',
        'target',
        'hyperparameters',
        'metrics',
        'model_path',
        'feature_importance',
        'last_trained_at',
        'deployed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'target' => 'array',
            'hyperparameters' => 'array',
            'metrics' => 'array',
            'feature_importance' => 'array',
            'last_trained_at' => 'datetime',
            'deployed_at' => 'datetime',
        ];
    }

    /** The user who created the model. */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Predictions made by this model. */
    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class)->latest();
    }

    /** Check if model is ready for predictions. */
    public function isReady(): bool
    {
        return $this->status === 'ready' && $this->model_path;
    }

    /** Check if model is training. */
    public function isTraining(): bool
    {
        return $this->status === 'training';
    }

    /** Get model performance metrics. */
    public function getPerformanceMetrics(): array
    {
        return $this->metrics ?? [
            'mae' => null,
            'rmse' => null,
            'r2' => null,
            'mape' => null,
        ];
    }

    /** Scope for ready models. */
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    /** Scope by type. */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
