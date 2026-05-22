<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHelpRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    'pending',
                    'tasdiqlandi',
                    'rad_etildi',
                    'rezerv',
                    'approved',
                    'rejected',
                ]),
            ],
            'admin_notes' => ['nullable', 'string'],
        ];
    }
}
