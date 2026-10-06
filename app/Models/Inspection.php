<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = ['equipment_id', 'rental_id', 'inspection_date', 'condition_before', 'condition_after', 'damage_detected', 'comments'];

    protected function casts(): array
    {
        return ['inspection_date' => 'date', 'damage_detected' => 'boolean'];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}
