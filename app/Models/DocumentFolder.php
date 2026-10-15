<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Document folder for organization.
 */
class DocumentFolder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'document_folders';

    protected $fillable = [
        'parent_id',
        'user_id',
        'name',
        'color',
        'icon',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /** The parent folder (for nested folders). */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocumentFolder::class, 'parent_id');
    }

    /** Child folders. */
    public function children(): HasMany
    {
        return $this->hasMany(DocumentFolder::class, 'parent_id')->orderBy('sort_order');
    }

    /** The folder owner. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Documents in this folder. */
    public function items(): HasMany
    {
        return $this->hasMany(DocumentFolderItem::class);
    }

    /** Documents in this folder (through pivot). */
    public function documents()
    {
        return $this->belongsToMany(Document::class, 'document_folder_items')
            ->withTimestamps();
    }
}
