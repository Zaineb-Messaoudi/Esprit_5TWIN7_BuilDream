<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Translation key for grouping translations.
 */
class TranslationKey extends Model
{
    use HasFactory;

    protected $table = 'translation_keys';

    protected $fillable = [
        'key',
        'description',
        'group',
    ];

    /** Translations for this key. */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

    /** Scope by group. */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
