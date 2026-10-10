<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Currency exchange rate for a specific date.
 */
class CurrencyExchangeRate extends Model
{
    use HasFactory;

    protected $table = 'currency_exchange_rates';

    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'rate',
        'date',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:8',
            'date' => 'date',
        ];
    }

    /** The source currency. */
    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    /** The target currency. */
    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    /** Get exchange rate for a currency pair on a specific date. */
    public static function getRate(Currency $from, Currency $to, ?Carbon $date = null): float
    {
        if ($from->id === $to->id) {
            return 1.0;
        }

        $date = $date ?? now();

        // Try exact date
        $rate = static::where('from_currency_id', $from->id)
            ->where('to_currency_id', $to->id)
            ->where('date', '<=', $date)
            ->orderByDesc('date')
            ->first();

        if ($rate) {
            return (float) $rate->rate;
        }

        // Try reverse rate
        $reverseRate = static::where('from_currency_id', $to->id)
            ->where('to_currency_id', $from->id)
            ->where('date', '<=', $date)
            ->orderByDesc('date')
            ->first();

        if ($reverseRate) {
            return 1 / (float) $reverseRate->rate;
        }

        // Fallback to stored exchange_rate on currency model
        return $to->exchange_rate / $from->exchange_rate;
    }

    /** Scope for latest rates. */
    public function scopeLatest($query)
    {
        return $query->latest('date');
    }

    /** Scope for specific date. */
    public function scopeForDate($query, $date)
    {
        return $query->where('date', '<=', $date)->latest('date');
    }

    /** Scope for currency pair. */
    public function scopeForPair($query, int $fromId, int $toId)
    {
        return $query->where('from_currency_id', $fromId)
            ->where('to_currency_id', $toId);
    }
}
