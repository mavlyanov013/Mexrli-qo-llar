<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasLocalizedAttributes;

    public array $localizedAttributes = [
        'question',
        'answer',
    ];

    protected $fillable = [
        'question',
        'question_uz',
        'question_oz',
        'question_ru',
        'answer',
        'answer_uz',
        'answer_oz',
        'answer_ru',
        'category',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
