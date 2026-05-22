<?php

namespace App\Http\Requests\Admin;

use App\Support\LocalizedContent;
use Illuminate\Foundation\Http\FormRequest;

class StoreTreatmentProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            [
                'case_id' => ['required', 'integer', 'exists:case_items,id'],
                'images' => ['nullable', 'array'],
                'images.*' => ['nullable', 'string', 'max:500'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
            ],
            LocalizedContent::adminValidationRules('title', true, 255),
            LocalizedContent::adminValidationRules('description', true)
        );
    }

    protected function prepareForValidation(): void
    {
        $this->merge(
            LocalizedContent::syncLegacyColumns(
                LocalizedContent::prepareAdminLocalized($this->all(), ['title', 'description']),
                ['title', 'description']
            )
        );
    }
}
