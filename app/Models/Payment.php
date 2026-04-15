<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'legacy_mongo_id',
        'legacy_local_id',

        'provider',
        'transaction_id',
        'status',
        'category',

        'payer_reference',

        'amount',
        'currency',
        'refunded_amount',

        'external_id',
        'service_id',

        'provider_time_ms',
        'provider_create_time',
        'provider_perform_time',
        'provider_cancel_time',

        'live_mode',
        'payload',
        'raw_information',

        'donation_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'refunded_amount' => 'float',
        'live_mode' => 'boolean',
        'payload' => 'array',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';
    public const STATUS_FUNDED = 'funded';

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getPaycomState(): int
    {
        if ($this->status === self::STATUS_PENDING) {
            return 1;
        }

        if ($this->status === self::STATUS_SUCCESS) {
            return 2;
        }

        if ($this->status === self::STATUS_CANCELLED) {
            return $this->provider_perform_time ? -2 : -1;
        }

        return -2;
    }
    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }
}
