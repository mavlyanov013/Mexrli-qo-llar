<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasLocalizedAttributes;

    public array $localizedAttributes = [
        'name',
        'position',
    ];

    protected $fillable = [
        'name',
        'name_uz',
        'name_oz',
        'name_ru',
        'position',
        'position_uz',
        'position_oz',
        'position_ru',
        'photo',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
