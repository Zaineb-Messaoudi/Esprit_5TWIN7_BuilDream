<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Document version history.
 */
class DocumentVersion extends Model
{
    use HasFactory;

    protected $table = 'document_versions';

    protected $fillable = [
        'document_id',
        'version',
        'content',
        'variables',
        'change_summary',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
        ];
    }

    /** The document this version belongs to. */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** The user who created this version. */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
