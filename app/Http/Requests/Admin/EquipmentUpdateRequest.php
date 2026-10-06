<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EquipmentUpdateRequest extends EquipmentStoreRequest
{
    public function rules(): array
    {
        return self::equipmentRules((int) $this->route('equipment'));
    }
}
