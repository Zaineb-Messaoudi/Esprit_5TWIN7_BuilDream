<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit log of administrative actions.
 */
class AdminAction extends Model
{
    use HasFactory;

    protected $table = 'admin_actions';

    protected $fillable = [
        'admin_id',
        'action_type',
        'target_type',
        'target_id',
        'reason',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /** The admin who performed the action. */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /** Get the target model. */
    public function getTarget(): ?Model
    {
        if (! $this->target_type || ! $this->target_id) {
            return null;
        }

        try {
            return $this->target_type::find($this->target_id);
        } catch (\Exception $e) {
            return null;
        }
    }

    /** Scope for actions by admin. */
    public function scopeByAdmin($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /** Scope for actions by type. */
    public function scopeType($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /** Scope for recent actions. */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
