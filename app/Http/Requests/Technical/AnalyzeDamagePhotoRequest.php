<?php

namespace App\Http\Requests\Technical;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeDamagePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('technical-access') ?? false;
    }

    public function rules(): array
    {
        return ['photo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']];
    }
}
