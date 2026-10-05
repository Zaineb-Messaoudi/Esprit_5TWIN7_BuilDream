<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique(Category::class, 'name')->ignore($this->route('category'))],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
