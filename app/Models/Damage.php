<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Damage extends Model
{
    use HasFactory;

    protected $fillable = ['inspection_id', 'equipment_id', 'inspection_checklist_item_id', 'severity', 'repair_cost', 'description', 'status'];

    protected function casts(): array
    {
        return ['repair_cost' => 'decimal:2'];
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(InspectionChecklistItem::class, 'inspection_checklist_item_id');
    }
}
