<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Document template with placeholders and signature fields.
 */
class DocumentTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'document_templates';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'content',
        'variables',
        'signature_fields',
        'is_active',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'signature_fields' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /** Documents created from this template. */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /** Scope for active templates. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Scope by category. */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /** Get variable schema. */
    public function getVariableSchema(): array
    {
        return $this->variables ?? [];
    }

    /** Get signature field definitions. */
    public function getSignatureFields(): array
    {
        return $this->signature_fields ?? [];
    }
}
