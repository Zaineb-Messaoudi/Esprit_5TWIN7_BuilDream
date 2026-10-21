<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationAssistantRequest extends Model
{
    protected $fillable = [
        'reference',
        'user_id',
        'service_type',
        'custom_description',
        'surface_m2',
        'urgency',
        'budget',
        'address',
        'city',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'surface_m2' => 'decimal:2',
            'budget' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
