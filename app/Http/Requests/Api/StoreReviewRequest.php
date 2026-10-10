<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for creating a review.
 */
class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ratings' => ['required', 'array'],
            'ratings.overall' => ['required', 'integer', 'between:1,5'],
            'ratings.communication' => ['nullable', 'integer', 'between:1,5'],
            'ratings.reliability' => ['nullable', 'integer', 'between:1,5'],
            'ratings.condition' => ['nullable', 'integer', 'between:1,5'],
            'ratings.value' => ['nullable', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['string', 'url', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'ratings.overall.required' => 'Overall rating is required.',
            'ratings.overall.between' => 'Overall rating must be between 1 and 5.',
            'comment.max' => 'Comment cannot exceed 2000 characters.',
            'photos.max' => 'Maximum 5 photos allowed.',
            'photos.*.url' => 'Photo URLs must be valid URLs.',
        ];
    }
}
