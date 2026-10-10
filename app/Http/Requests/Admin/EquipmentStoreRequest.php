<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Http\Requests\Concerns\ValidatesEnergyProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validates administrator-created equipment and nested energy data. */
class EquipmentStoreRequest extends FormRequest
{
    use ValidatesEnergyProfile;

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $equipment = $this->route('equipment');
        $this->mergeIfMissing([
            'approval_status' => $equipment?->approval_status ?? 'published',
        ]);
    }

    public function rules(): array
    {
        return self::equipmentRules();
    }

    public static function equipmentRules(?int $equipmentId = null): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'owner_id' => [Rule::requiredIf(fn () => true), Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', UserRole::OWNER->value))],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:3000'],
            'image_url' => ['nullable', 'url:http,https', 'max:2048'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'price_per_day' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'condition' => ['required', 'in:new,good,fair,damaged'],
            'location' => ['required', 'string', 'max:150'],
            'status' => ['required', 'in:available,unavailable,maintenance,inactive'],
            'approval_status' => ['required', 'in:pending_review,published,rejected'],
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
