<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * User request for data export (GDPR Article 20).
 */
class DataExportRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'data_export_requests';

    protected $fillable = [
        'user_id',
        'status',
        'include_categories',
        'exclude_categories',
        'date_range_start',
        'date_range_end',
        'format',
        'file_path',
        'file_size',
        'record_count',
        'completed_at',
        'downloaded_at',
        'expires_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'include_categories' => 'array',
            'exclude_categories' => 'array',
            'date_range_start' => 'date',
            'date_range_end' => 'date',
            'completed_at' => 'datetime',
            'downloaded_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /** Check if request is ready for download. */
    public function isReady(): bool
    {
        return $this->status === 'ready' && $this->file_path;
    }

    /** Check if request has expired. */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /** Mark as ready for download. */
    public function markReady(string $filePath, int $fileSize, int $recordCount): void
    {
        $this->update([
            'status' => 'ready',
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'record_count' => $recordCount,
            'completed_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);
    }

    /** Mark as downloaded. */
    public function markDownloaded(): void
    {
        $this->update([
            'status' => 'downloaded',
            'downloaded_at' => now(),
        ]);
    }

    /** Scope for pending requests. */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
