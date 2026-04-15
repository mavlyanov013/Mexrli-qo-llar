<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'service_type',
        'donor_name',
        'donor_email',
        'amount',
        'currency',
        'type',
        'message',
        'is_anonymous',
        'status',
        'legacy_payment_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseItem::class, 'case_id');
    }
}
