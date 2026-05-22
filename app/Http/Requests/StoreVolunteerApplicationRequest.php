<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVolunteerApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'regex:/^\+998[0-9]{9}$/'],
            'city' => ['nullable', 'string', 'max:255'],
            'role_interest' => [
                'nullable',
                Rule::in([
                    'medical_support',
                    'fundraising',
                    'events',
                    'translation',
                    'social_media',
                    'mentoring',
                    'other',
                ]),
            ],
            'experience' => ['nullable', 'string'],
            'motivation' => ['nullable', 'string'],
            'message' => 'nullable|string',
            'availability' => [
                'nullable',
                Rule::in(['full_time', 'part_time', 'weekends', 'flexible']),
            ],
        ];
    }
}
