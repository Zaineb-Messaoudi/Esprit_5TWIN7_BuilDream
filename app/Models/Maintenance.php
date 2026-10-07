<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = ['equipment_id', 'rental_id', 'start_date', 'end_date', 'reason', 'cost', 'status', 'notes'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'cost' => 'decimal:2'];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(MaintenanceReport::class);
    }
}
