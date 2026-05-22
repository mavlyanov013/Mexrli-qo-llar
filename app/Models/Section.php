<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\SectionType;

class Section extends Model
{
    protected $fillable = [
        'page_id',
        'type',
        'title',
        'subtitle',
        'content',
        'image',
        'file_path',
        'file_name',
        'sort_order',
        'extra',
    ];

    protected $casts = [
//        'type' => SectionType::class,
        'extra' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function isPaid(): bool
    {
        return filter_var(data_get($this->extra, 'paid'), FILTER_VALIDATE_BOOLEAN);
    }
}
