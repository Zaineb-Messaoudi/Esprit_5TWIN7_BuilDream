<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Currency model with exchange rates.
 */
class Currency extends Model
{
    use HasFactory;

    protected $table = 'currencies';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'symbol_native',
        'decimal_places',
        'exchange_rate',
        'is_base',
        'is_active',
        'is_crypto',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:8',
            'decimal_places' => 'integer',
            'is_base' => 'boolean',
            'is_active' => 'boolean',
            'is_crypto' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /** Exchange rates from this currency. */
    public function exchangeRatesFrom(): HasMany
    {
        return $this->hasMany(CurrencyExchangeRate::class, 'from_currency_id');
    }

    /** Exchange rates to this currency. */
    public function exchangeRatesTo(): HasMany
    {
        return $this->hasMany(CurrencyExchangeRate::class, 'to_currency_id');
    }

    /** Get the base currency. */
    public static function getBase(): ?self
    {
        return static::where('is_base', true)->where('is_active', true)->first();
    }

    /** Format an amount in this currency. */
    public function format(float $amount): string
    {
        $formatted = number_format($amount, $this->decimal_places, '.', '');

        if ($this->symbol_native && app()->getLocale() !== 'en') {
            return $this->symbol_native.$formatted;
        }

        return $this->symbol.$formatted;
    }

    /** Convert amount from this currency to another. */
    public function convertTo(float $amount, Currency $targetCurrency, ?\Illuminate\Support\Carbon $date = null): float
    {
        if ($this->id === $targetCurrency->id) {
            return $amount;
        }

        $rate = CurrencyExchangeRate::getRate($this, $targetCurrency, now());

        return round($amount * $rate, $targetCurrency->decimal_places);
    }

    /** Scope for active currencies. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Get currency symbol for locale. */
    public function getSymbolForLocale(string $locale): string
    {
        if ($locale !== 'en' && $this->symbol_native) {
            return $this->symbol_native;
        }

        return $this->symbol;
    }
}
