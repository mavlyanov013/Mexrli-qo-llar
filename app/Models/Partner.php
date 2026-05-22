<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasLocalizedAttributes;

    public array $localizedAttributes = [
        'name',
        'description',
    ];

    protected $fillable = [
        'name',
        'name_uz',
        'name_oz',
        'name_ru',
        'logo_url',
        'website',
        'description',
        'description_uz',
        'description_oz',
        'description_ru',
        'type',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];
}
