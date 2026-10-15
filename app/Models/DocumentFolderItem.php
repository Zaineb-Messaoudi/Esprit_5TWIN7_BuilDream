<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot table for document-folder relationship.
 */
class DocumentFolderItem extends Model
{
    use HasFactory;

    protected $table = 'document_folder_items';

    protected $fillable = [
        'folder_id',
        'document_id',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(DocumentFolder::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
