<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Technical specifications associated with one equipment listing. */
class EnergyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id', 'power_watts', 'voltage', 'capacity_wh', 'efficiency',
        'technology', 'max_output', 'operating_duration',
    ];

    protected function casts(): array
    {
        return [
            'voltage' => 'decimal:2',
            'efficiency' => 'decimal:2',
            'operating_duration' => 'decimal:2',
        ];
    }

    /** The equipment described by this profile. */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
