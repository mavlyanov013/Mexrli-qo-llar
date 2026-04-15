<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'case_id' => ['nullable', 'exists:cases,id'],
            'donor_name' => ['nullable', 'string', 'max:255'],
            'donor_email' => ['nullable', 'email', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['nullable', 'string', 'max:10'],
            'type' => ['nullable', Rule::in(['one_time', 'monthly'])],
            'message' => ['nullable', 'string'],
            'is_anonymous' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(['pending', 'completed', 'failed'])],
        ];
    }
}
