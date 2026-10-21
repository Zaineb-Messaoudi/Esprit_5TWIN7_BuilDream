<?php

namespace App\Http\Requests\Technical;

use Illuminate\Foundation\Http\FormRequest;

class TranscribeInspectionAudioRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('technical-access') ?? false; }

    public function rules(): array
    {
        return ['audio' => ['required', 'file', 'mimetypes:audio/mpeg,audio/wav,audio/x-wav,audio/webm,audio/mp4', 'max:10240']];
    }
}
