<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class TodayStatsService
{
    private const COMPLETED_STATUSES = ['completed', 'success'];

    private const CASH_DONATION_TYPES = ['naxt', 'manual'];

    private const CASH_PROVIDERS = ['cash', 'naxt'];

    public function get(): array
    {
        $today = Carbon::today();

        $cashTotal = $this->cashTotal($today);
        $onlineTotal = $this->onlineTotal($today);
        $donorsCount = $this->donorsCount($today);

        return [
            'cash_total' => round($cashTotal, 2),
            'online_total' => round($onlineTotal, 2),
            'donors_count' => $donorsCount,
            'updated_at' => now()->toIso8601String(),
        ];
    }

    private function cashTotal(Carbon $today): float
    {
        return (float) Donation::query()
            ->whereDate('created_at', $today)
            ->whereIn('status', self::COMPLETED_STATUSES)
            ->whereIn('type', self::CASH_DONATION_TYPES)
            ->sum('amount');
    }

    private function onlineTotal(Carbon $today): float
    {
        return (float) Payment::query()
            ->whereDate('created_at', $today)
            ->whereIn('status', self::COMPLETED_STATUSES)
            ->whereRaw("LOWER(COALESCE(provider, '')) NOT IN (?, ?)", self::CASH_PROVIDERS)
            ->sum('amount');
    }

    private function donorsCount(Carbon $today): int
    {
        $keys = [];

        Donation::query()
            ->whereDate('created_at', $today)
            ->whereIn('status', self::COMPLETED_STATUSES)
            ->get(['donor_phone', 'donor_name', 'is_anonymous'])
            ->each(function (Donation $donation) use (&$keys) {
                $key = $this->donorKey($donation);

                if ($key) {
                    $keys[$key] = true;
                }
            });

        Payment::query()
            ->whereDate('created_at', $today)
            ->whereIn('status', self::COMPLETED_STATUSES)
            ->whereRaw("LOWER(COALESCE(provider, '')) NOT IN (?, ?)", self::CASH_PROVIDERS)
            ->with(['donation:id,donor_phone,donor_name,is_anonymous'])
            ->get(['id', 'payer_reference', 'donation_id'])
            ->each(function (Payment $payment) use (&$keys) {
                if ($payment->donation) {
                    $key = $this->donorKey($payment->donation);

                    if ($key) {
                        $keys[$key] = true;

                        return;
                    }
                }

                $reference = trim((string) ($payment->payer_reference ?? ''));

                if ($reference !== '') {
                    $keys[$reference] = true;
                }
            });

        return count($keys);
    }

    private function donorKey(Donation $donation): ?string
    {
        if ($donation->is_anonymous) {
            return null;
        }

        $phone = trim((string) ($donation->donor_phone ?? ''));
        $name = trim((string) ($donation->donor_name ?? ''));

        return $phone !== '' ? $phone : ($name !== '' ? $name : null);
    }
}
