<?php

namespace App\Http\Requests\Technical;

use App\Models\Maintenance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenancePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $maintenance = $this->route('maintenance');

        return $maintenance instanceof Maintenance && (bool) $this->user()?->can('update', $maintenance);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['before', 'after'])],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
