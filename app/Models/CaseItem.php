<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseItem extends Model
{
    use HasFactory;
    use HasLocalizedAttributes;

    public array $localizedAttributes = [
        'name',
        'location',
        'condition',
        'story',
        'short_description',
    ];

    protected $fillable = [
        'name',
        'name_uz',
        'name_oz',
        'name_ru',
        'age',
        'photo_url',
        'location',
        'location_uz',
        'location_oz',
        'location_ru',
        'phone',
        'condition',
        'condition_uz',
        'condition_oz',
        'condition_ru',
        'story',
        'story_uz',
        'story_oz',
        'story_ru',
        'short_description',
        'short_description_uz',
        'short_description_oz',
        'short_description_ru',
        'goal_amount',
        'raised_amount',
        'urgency',
        'category',
        'source',
        'created_from_request_id',
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

    public function treatmentProcesses(): HasMany
    {
        return $this->hasMany(TreatmentProcess::class, 'case_id');
    }
}
