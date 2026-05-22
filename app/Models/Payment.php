<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'legacy_mongo_id',
        'legacy_local_id',
        'provider',
        'method',
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
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'live_mode' => 'boolean',
        'payload' => 'array',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';
    public const STATUS_FUNDED = 'funded';

    public const PROVIDER_CASH = 'cash';

    public function scopeOnlineOnly(Builder $query): Builder
    {
        return $query
            ->whereRaw("LOWER(COALESCE(provider, '')) NOT IN ('cash', 'naxt')")
            ->where(function (Builder $inner) {
                $inner->whereNull('method')
                    ->orWhereRaw("LOWER(COALESCE(method, '')) NOT IN ('cash', 'naxt')");
            });
    }

    public function isCashPayment(): bool
    {
        $provider = strtolower((string) $this->provider);
        $method = strtolower((string) ($this->method ?? ''));

        return in_array($provider, [self::PROVIDER_CASH, 'naxt'], true)
            || in_array($method, [self::PROVIDER_CASH, 'naxt'], true);
    }

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

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }
}
