<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceReport extends Model
{
    use HasFactory;

    protected $fillable = ['maintenance_id', 'diagnosis', 'actions_taken', 'parts_replaced', 'technician_notes', 'report_date'];

    protected function casts(): array
    {
        return ['report_date' => 'date'];
    }

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class);
    }
}
