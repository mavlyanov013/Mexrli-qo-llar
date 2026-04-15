<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'age',
        'photo_url',
        'location',
        'condition',
        'story',
        'short_description',
        'goal_amount',
        'raised_amount',
        'urgency',
        'category',
        'status',
        'medical_documents',
        'updates',
        'is_featured',
        'is_urgent',
    ];

    protected $casts = [
        'medical_documents' => 'array',
        'updates' => 'array',
        'is_featured' => 'boolean',
        'is_urgent' => 'boolean',
        'goal_amount' => 'decimal:2',
        'raised_amount' => 'decimal:2',
    ];

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'case_id');
    }

    public function helpRequests(): HasMany
    {
        return $this->hasMany(HelpRequest::class, 'case_id');
    }
}
