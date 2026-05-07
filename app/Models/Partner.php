<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'name',
        'logo_url',
        'website',
        'description',
        'type',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];
}
