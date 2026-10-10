<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User language and currency preferences.
 */
class LanguagePreference extends Model
{
    use HasFactory;

    protected $table = 'language_preferences';

    protected $fillable = [
        'user_id',
        'locale',
        'is_rtl',
        'currency_code',
        'timezone',
        'date_format',
        'number_format',
    ];

    protected function casts(): array
    {
        return [
            'is_rtl' => 'boolean',
            'date_format' => 'array',
            'number_format' => 'array',
        ];
    }

    /** The user. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Get the currency. */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    /** Get date format for locale. */
    public function getDateFormat(): string
    {
        return $this->date_format['format'] ?? 'd/m/Y';
    }

    /** Get number format for locale. */
    public function getNumberFormat(): array
    {
        return $this->number_format ?? [
            'decimal' => '.',
            'thousands' => ',',
        ];
    }

    /** Get locale object. */
    public function getLocaleObject(): \Illuminate\Support\Carbon
    {
        return \Illuminate\Support\Carbon::now()->locale($this->locale);
    }
}
