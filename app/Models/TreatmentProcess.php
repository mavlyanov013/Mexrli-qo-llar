<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentProcess extends Model
{
    use HasFactory;
    use HasLocalizedAttributes;

    public array $localizedAttributes = [
        'title',
        'description',
    ];

    protected $fillable = [
        'case_id',
        'title',
        'title_uz',
        'title_oz',
        'title_ru',
        'description',
        'description_uz',
        'description_oz',
        'description_ru',
        'images',
        'sort_order',
    ];

    protected $casts = [
        'images' => 'array',
        'sort_order' => 'integer',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseItem::class, 'case_id');
    }
}
