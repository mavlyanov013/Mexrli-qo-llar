<?php

namespace App\Console\Commands;

use App\Models\Donation;
use App\Models\Payment;
use Illuminate\Console\Command;

class ConvertPaymentsToDonations extends Command
{
    protected $signature = 'payments:to-donations';
    protected $description = 'Convert successful payments to donations';

    public function handle(): int
    {
        $payments = Payment::query()
            ->where('status', 'success')
            ->orderBy('id')
            ->get();

        $count = 0;
        $skipped = 0;

        foreach ($payments as $payment) {
            if (Donation::query()->where('legacy_payment_id', $payment->id)->exists()) {
                $skipped++;
                continue;
            }

            Donation::query()->create([
                'legacy_payment_id' => $payment->id,
                'case_id' => null,
                'service_type' => $payment->category ?? 'general',
                'donor_name' => $this->extractName($payment),
                'donor_email' => $this->extractEmail($payment),
                'amount' => $payment->amount,
                'currency' => $payment->currency ?: 'UZS',
                'type' => 'one_time',
                'message' => null,
                'is_anonymous' => false,
                'status' => 'completed',
            ]);

            $count++;
        }

        $this->info("Created donations: {$count}");
        $this->info("Skipped: {$skipped}");

        return self::SUCCESS;
    }

    private function extractName(Payment $payment): string
    {
        $payload = is_array($payment->payload) ? $payment->payload : [];

        if (!empty($payload['donor_name'])) {
            return (string) $payload['donor_name'];
        }

        if (!empty($payload['user_data'])) {
            return (string) $payload['user_data'];
        }

        if (!empty($payment->payer_reference)) {
            return (string) $payment->payer_reference;
        }

        return 'Anonymous';
    }

    private function extractEmail(Payment $payment): ?string
    {
        $payload = is_array($payment->payload) ? $payment->payload : [];

        if (!empty($payload['donor_email'])) {
            return (string) $payload['donor_email'];
        }

        return null;
    }
}
