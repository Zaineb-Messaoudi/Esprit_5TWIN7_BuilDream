<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for creating a document.
 */
class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template_id' => ['nullable', 'exists:document_templates,id'],
            'title' => ['required_without:template_id', 'string', 'max:255'],
            'type' => ['nullable', 'in:contract,agreement,policy,invoice,certificate,report,other'],
            'content' => ['required_without:template_id', 'string'],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['string'],
            'metadata' => ['nullable', 'array'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'signature_fields' => ['nullable', 'array'],
            'signature_fields.*.name' => ['required', 'string'],
            'signature_fields.*.email' => ['nullable', 'email'],
            'signature_fields.*.role' => ['required', 'in:owner,renter,witness,admin,external'],
            'signature_fields.*.signer_id' => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'template_id.exists' => 'Selected template does not exist.',
            'content.required_without' => 'Content is required when not using a template.',
        ];
    }
}
