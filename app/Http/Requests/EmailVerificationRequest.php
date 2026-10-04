<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && (string) $user->getKey() === (string) $this->route('id')
            && hash_equals(sha1($user->getEmailForVerification()), (string) $this->route('hash'));
    }

    public function rules(): array
    {
        return [];
    }
}
