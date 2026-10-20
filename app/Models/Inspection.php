<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = ['equipment_id', 'rental_id', 'inspection_checklist_id', 'inspection_date', 'condition_before', 'condition_after', 'damage_detected', 'checklist_results', 'comments'];

    protected function casts(): array
    {
        return ['inspection_date' => 'date', 'damage_detected' => 'boolean', 'checklist_results' => 'array'];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(InspectionChecklist::class, 'inspection_checklist_id');
    }

    public function damages(): HasMany
    {
        return $this->hasMany(Damage::class);
    }
}
