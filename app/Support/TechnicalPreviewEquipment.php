<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/** Local development adapter used only until the real Equipment model is merged. */
class TechnicalPreviewEquipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = ['name', 'owner_id', 'technical_preview'];

    protected function casts(): array
    {
        return ['technical_preview' => 'boolean'];
    }
}
