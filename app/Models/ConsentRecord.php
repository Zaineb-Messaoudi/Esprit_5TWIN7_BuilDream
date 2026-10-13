<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * User consent record for GDPR compliance.
 */
class ConsentRecord extends Model
{
    use HasFactory;

    protected $table = 'consent_records';

    protected $fillable = [
        'user_id',
        'consent_type',
        'granted',
        'version',
        'consent_text',
        'ip_address',
        'user_agent',
        'granted_at',
        'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'granted' => 'boolean',
            'granted_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    /** Check if consent is currently granted. */
    public function isActive(): bool
    {
        return $this->granted && ! $this->withdrawn_at;
    }

    /** Withdraw consent. */
    public function withdraw(): void
    {
        $this->update([
            'granted' => false,
            'withdrawn_at' => now(),
        ]);
    }

    /** Scope for active consents. */
    public function scopeActive($query)
    {
        return $query->where('granted', true)->whereNull('withdrawn_at');
    }
}
