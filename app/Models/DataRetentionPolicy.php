<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Data retention policy for GDPR compliance.
 */
class DataRetentionPolicy extends Model
{
    use HasFactory;

    protected $table = 'data_retention_policies';

    protected $fillable = [
        'name',
        'category',
        'retention_days',
        'action_on_expiry',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'retention_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** Get expiry date for a given creation date. */
    public function getExpiryDate(\Illuminate\Support\Carbon $createdAt): \Illuminate\Support\Carbon
    {
        return $createdAt->copy()->addDays($this->retention_days);
    }

    /** Check if a record has expired. */
    public function hasExpired(\Illuminate\Support\Carbon $createdAt): bool
    {
        return $this->getExpiryDate($createdAt) <= now();
    }

    /** Scope for active policies. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
