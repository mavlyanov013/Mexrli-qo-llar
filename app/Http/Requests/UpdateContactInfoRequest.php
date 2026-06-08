<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'regex:/^\+998[0-9]{9}$/'],
            'phone_2' => ['nullable', 'regex:/^\+998[0-9]{9}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'map_embed_url' => ['nullable', 'string', 'max:2000'],
            'map_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'map_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'instagram_url' => ['nullable', 'url', 'max:500'],
            'youtube_url' => ['nullable', 'url', 'max:500'],
            'facebook_url' => ['nullable', 'url', 'max:500'],
            'telegram_url' => ['nullable', 'url', 'max:500'],
        ];
    }
}
