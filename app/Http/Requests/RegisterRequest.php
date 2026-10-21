<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Legacy callers may still submit the single name field.
            // The browser form sends fname/lname and prepareForValidation joins them.
            'fname' => ['nullable', 'string', 'max:100'],
            'lname' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+().\\s-]+$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'address_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'address_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = trim(implode(' ', array_filter([
            $this->input('fname'),
            $this->input('lname'),
        ])));

        if ($name !== '') {
            $this->merge(['name' => $name]);
        }
    }
}
