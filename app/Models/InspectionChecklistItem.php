<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = ['inspection_checklist_id', 'label', 'is_critical', 'sort_order'];

    protected function casts(): array
    {
        return ['is_critical' => 'boolean'];
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
