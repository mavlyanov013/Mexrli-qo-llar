<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVolunteerApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'phone' => ['nullable', 'regex:/^\+998[0-9]{9}$/'],
            'city' => ['nullable', 'string', 'max:255'],
            'role_interest' => [
                'sometimes',
                'string',
                'max:100',
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
            'availability' => [
                'nullable',
                Rule::in(['full_time', 'part_time', 'weekends', 'flexible']),
            ],
            'status' => ['sometimes', 'string', Rule::in(['tasdiqlandi', 'rad_etildi', 'rezerv'])],
            'admin_notes' => ['nullable', 'string'],
        ];
    }
}
