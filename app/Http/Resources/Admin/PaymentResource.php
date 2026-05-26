<?php

namespace App\Http\Resources\Admin;

use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Legacy admin API returns a raw array (no "data" wrapper).
     */
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $payload = $this->decodedPayload();
        /** @var Donation|null $donation */
        $donation = $this->relationLoaded('donation') ? $this->donation : null;

        $donorName = $payload['donor_name'] ?? $donation?->donor_name;

        return [
            'id' => $this->id,
            'donation_id' => $this->donation_id,
            'provider' => $this->provider,
            'transaction_id' => $this->transaction_id,
            'external_id' => $this->external_id,
            'amount' => $this->numericAmount($this->amount),
            'currency' => $this->currency ?? 'UZS',
            'status' => $this->status,
            'donor_name' => $donorName,
            'is_anonymous' => (bool) ($payload['is_anonymous'] ?? $donation?->is_anonymous ?? false),
            'service_type' => $payload['service_type'] ?? $donation?->service_type,
            'case_id' => $payload['case_id'] ?? $donation?->case_id,
            'payload' => $payload === [] ? (object) [] : $payload,
            'donation' => $this->mapDonation($donation),
            'created_at' => $this->legacyTimestamp($this->created_at),
            'updated_at' => $this->legacyTimestamp($this->updated_at),
        ];
    }

    /**
     * @return array<string, mixed>|object
     */
    private function decodedPayload(): array
    {
        $payload = $this->payload;

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($payload) ? $payload : [];
    }

    private function mapDonation(?Donation $donation): ?array
    {
        if ($donation === null) {
            return null;
        }

        return [
            'id' => $donation->id,
            'donor_name' => $donation->donor_name,
            'amount' => $this->numericAmount($donation->amount),
            'status' => $donation->status,
            'case_id' => $donation->case_id,
        ];
    }

    private function numericAmount(mixed $amount): float
    {
        return (float) $amount;
    }

    private function legacyTimestamp(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value->utc()->format('Y-m-d\TH:i:s.u\Z');
    }
}
