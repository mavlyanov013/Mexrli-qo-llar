<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpRequest extends Model
{
    protected $fillable = [
        'full_name',
        'phone',
        'description',
        'city',
        'situation_description',
        'category',
        'support_type',
        'attachments',
        'medical_documents',
        'photos',
        'consent_given',
        'status',
        'admin_notes',
        'case_id',
    ];

    protected $casts = [
        'attachments' => 'array',
        'medical_documents' => 'array',
        'photos' => 'array',
        'consent_given' => 'boolean',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseItem::class, 'case_id');
    }
}
