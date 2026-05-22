<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHelpRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^\+998[0-9]{9}$/'],
            'description' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'situation_description' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:120'],
            'support_type' => [
                'nullable',
                Rule::in([
                    'medical_treatment',
                    'surgery',
                    'rehabilitation',
                    'medication',
                    'family_support',
                    'other',
                ]),
            ],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['string'],
            'medical_documents' => ['nullable', 'array'],
            'medical_documents.*' => ['string'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['string'],
            'consent_given' => ['nullable', 'boolean'],
        ];
    }
}
