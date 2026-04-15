<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;

class ImportLegacyPayments extends Command
{
    protected $signature = 'legacy:import-payments {--limit=0} {--dry-run}';
    protected $description = 'Import legacy Mongo payments into PostgreSQL payments table';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        $collection = DB::connection('legacy_mongo')
            ->getMongoDB()
            ->selectCollection('payment');

        $cursor = $collection->find([], [
            'sort' => ['_id' => 1],
        ]);

        $count = 0;
        $inserted = 0;
        $skipped = 0;

        foreach ($cursor as $doc) {
            $count++;

            if ($limit > 0 && $count > $limit) {
                break;
            }

            $legacyMongoId = (string) ($doc['_id'] ?? '');
            $provider = (string) ($doc['method'] ?? 'unknown');
            $transactionId = (string) ($doc['transaction_id'] ?? '');

            if (!$transactionId) {
                $skipped++;
                $this->warn("Skipped {$legacyMongoId}: empty transaction_id");
                continue;
            }

            $exists = Payment::query()
                ->where('provider', $provider)
                ->where('transaction_id', $transactionId)
                ->exists();

            if ($exists) {
                $skipped++;
                $this->line("Skipped duplicate: {$provider} / {$transactionId}");
                continue;
            }

            $status = $this->mapStatus((string) ($doc['status'] ?? 'pending'));

            $payload = [
                'user_data' => $doc['user_data'] ?? null,
                'category' => $doc['category'] ?? null,
                'legacy_information' => $doc['information'] ?? null,
            ];

            $data = [
                'legacy_mongo_id' => $legacyMongoId,
                'legacy_local_id' => isset($doc['local_id']) ? (int) $doc['local_id'] : null,
                'provider' => $provider,
                'transaction_id' => $transactionId,
                'status' => $status,
                'category' => $doc['category'] ?? null,
                'payer_reference' => isset($doc['user_data']) ? (string) $doc['user_data'] : null,
                'amount' => $this->normalizeAmount($doc['amount'] ?? 0),
                'currency' => 'UZS',
                'refunded_amount' => 0,
                'external_id' => null,
                'service_id' => null,
                'provider_time_ms' => $this->toMillis($doc['time'] ?? null),
                'provider_create_time' => $this->toMillis($doc['create_time'] ?? null),
                'provider_perform_time' => $this->toMillis($doc['perform_time'] ?? null),
                'provider_cancel_time' => $this->toMillis($doc['cancel_time'] ?? null),
                'live_mode' => (bool) ($doc['live_mode'] ?? true),
                'payload' => $payload,
                'raw_information' => isset($doc['information']) ? (string) $doc['information'] : null,
                'created_at' => $this->toDateTime($doc['created_at'] ?? null) ?? now(),
                'updated_at' => $this->toDateTime($doc['updated_at'] ?? null) ?? now(),
            ];

            if ($dryRun) {
                $this->info("Would import: {$provider} / {$transactionId}");
                continue;
            }

            Payment::query()->create($data);
            $inserted++;

            $this->info("Imported: {$provider} / {$transactionId}");
        }

        $this->newLine();
        $this->info("Processed: {$count}");
        $this->info("Inserted: {$inserted}");
        $this->info("Skipped: {$skipped}");

        return self::SUCCESS;
    }

    protected function mapStatus(string $legacyStatus): string
    {
        return match (strtolower($legacyStatus)) {
            'success', 'completed', 'paid' => Payment::STATUS_SUCCESS,
            'cancelled', 'canceled' => Payment::STATUS_CANCELLED,
            'failed', 'error' => Payment::STATUS_FAILED,
            'funded' => Payment::STATUS_FUNDED,
            default => Payment::STATUS_PENDING,
        };
    }

    protected function normalizeAmount(mixed $amount): float
    {
        return is_numeric($amount) ? (float) $amount : 0.0;
    }

    protected function toMillis(mixed $value): ?int
    {
        if ($value instanceof UTCDateTime) {
            return $value->toDateTime()->getTimestamp() * 1000;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value)->getTimestampMs();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    protected function toDateTime(mixed $value): ?Carbon
    {
        if ($value instanceof UTCDateTime) {
            return Carbon::instance($value->toDateTime());
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value);
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
