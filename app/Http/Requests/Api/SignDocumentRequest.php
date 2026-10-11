<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for signing a document.
 */
class SignDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'signature_data' => ['required', 'string'],
            'metadata' => ['nullable', 'array'],
            'metadata.ip' => ['nullable', 'ip'],
            'metadata.user_agent' => ['nullable', 'string'],
            'metadata.geolocation' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'signature_data.required' => 'Signature data is required.',
        ];
    }
}
