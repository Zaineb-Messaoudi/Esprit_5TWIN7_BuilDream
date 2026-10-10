<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Translation value for a specific locale.
 */
class Translation extends Model
{
    use HasFactory;

    protected $table = 'translations';

    protected $fillable = [
        'translation_key_id',
        'locale',
        'value',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    /** The translation key. */
    public function key(): BelongsTo
    {
        return $this->belongsTo(TranslationKey::class, 'translation_key_id');
    }

    /** The user who verified this translation. */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /** Check if translation is verified. */
    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    /** Mark as verified. */
    public function verify(User $user): void
    {
        $this->update([
            'is_verified' => true,
            'verified_by' => $user->id,
            'verified_at' => now(),
        ]);
    }

    /** Scope by locale. */
    public function scopeLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    /** Scope for verified translations. */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
}
