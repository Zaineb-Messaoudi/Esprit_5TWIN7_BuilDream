<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for creating a chat message.
 */
class StoreChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:5000'],
            'type' => ['nullable', 'in:text,image,file,location,system'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*.type' => ['required', 'in:image,file'],
            'attachments.*.url' => ['required', 'url', 'max:500'],
            'attachments.*.name' => ['nullable', 'string', 'max:255'],
            'attachments.*.size' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Message is required.',
            'message.max' => 'Message cannot exceed 5000 characters.',
            'attachments.max' => 'Maximum 5 attachments allowed.',
            'attachments.*.url' => 'Attachment URL must be a valid URL.',
        ];
    }
}
