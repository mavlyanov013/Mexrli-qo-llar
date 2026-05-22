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

    protected function prepareForValidation(): void
    {
        if (! $this->is('api/v1/admin/*')) {
            return;
        }

        $this->merge([
            'type' => $this->input('type', 'naxt'),
            'status' => $this->input('status', 'completed'),
            'is_manual_cash' => $this->boolean('is_manual_cash', true),
        ]);
    }

    public function rules(): array
    {
        return [
            'case_id' => ['nullable', 'exists:cases,id'],
            'donor_name' => ['nullable', 'string', 'max:255'],
            'donor_email' => ['nullable', 'email', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['nullable', 'string', 'max:10'],
            'type' => ['nullable', Rule::in(['one_time', 'monthly', 'manual', 'naxt'])],
            'donor_phone' => ['nullable', 'regex:/^\+998[0-9]{9}$/'],
            'note' => ['nullable', 'string'],
            'is_manual_cash' => ['nullable', 'boolean'],
            'is_anonymous' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(['pending', 'completed', 'failed'])],
        ];
    }
}
