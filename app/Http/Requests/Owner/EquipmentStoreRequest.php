<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validates equipment published from the Owner Front Office. */
class EquipmentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware also checks the role; this protects direct request use.
        return $this->user()?->isOwner() ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('status', 'active'))],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:3000'],
            'image_url' => ['nullable', 'url:http,https', 'max:2048'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'price_per_day' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'condition' => ['required', 'in:new,good,fair,damaged'],
            'location' => ['required', 'string', 'max:150'],
            'status' => ['required', 'in:available,unavailable,maintenance,inactive'],
            'energy.power_watts' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'energy.voltage' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'energy.capacity_wh' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'energy.efficiency' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'energy.technology' => ['nullable', 'string', 'max:100'],
            'energy.max_output' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'energy.operating_duration' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ];
    }
}
