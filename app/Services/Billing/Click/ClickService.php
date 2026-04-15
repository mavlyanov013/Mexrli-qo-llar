<?php

namespace App\Services\Billing\Click;

use App\Models\Donation;
use App\Models\Payment;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ClickService
{
    public const ACTION_PREPARE = 0;
    public const ACTION_COMPLETE = 1;

    public function handle(array $payload): array
    {
        if (!isset($payload['action'])) {
            throw new HttpException(400, 'Action not found');
        }

        return match ((int) $payload['action']) {
            self::ACTION_PREPARE => $this->prepare($payload),
            self::ACTION_COMPLETE => $this->complete($payload),
            default => throw new HttpException(400, 'Action not found'),
        };
    }

    protected function prepare(array $payload): array
    {
        $this->validateBasePayload($payload, true);
        $this->validateSignature($payload, true);

        $clickTransId = (string) $payload['click_trans_id'];
        $merchantTransId = $this->decodeMerchantTransId($payload['merchant_trans_id']);
        $amount = (float) $payload['amount'];
        $donationId = (int) $merchantTransId;

        $payment = Payment::query()
            ->where('provider', 'click')
            ->where('transaction_id', $clickTransId)
            ->first();

        if (!$payment && $donationId) {
            $payment = Payment::query()
                ->where('provider', 'click')
                ->where('donation_id', $donationId)
                ->where('status', Payment::STATUS_PENDING)
                ->latest('id')
                ->first();
        }

        if ($payment) {
            if (round((float) $payment->amount, 2) !== round($amount, 2)) {
                throw new HttpException(400, 'Incorrect amount');
            }

            $payment->transaction_id = $clickTransId;
            $payment->payer_reference = $merchantTransId;
            $payment->external_id = (string) ($payload['click_paydoc_id'] ?? $payment->external_id);
            $payment->service_id = (string) ($payload['service_id'] ?? $payment->service_id);
            $payment->provider_time_ms = $this->parseSignTimeToMs($payload['sign_time'] ?? null);
            $payment->provider_create_time = $payment->provider_create_time ?: $this->currentTimestampMs();
            $payment->payload = array_merge($payment->payload ?? [], [
                'click_trans_id' => (string) ($payload['click_trans_id'] ?? ''),
                'click_paydoc_id' => (string) ($payload['click_paydoc_id'] ?? ''),
                'click_service_id' => (string) ($payload['service_id'] ?? ''),
                'click_sign_time' => $payload['sign_time'] ?? null,
                'click_sign_string' => $payload['sign_string'] ?? null,
                'merchant_trans_id' => $payload['merchant_trans_id'] ?? null,
            ]);
            $payment->save();

            return match ($payment->status) {
                Payment::STATUS_PENDING => [
                    'click_trans_id' => $payment->transaction_id,
                    'merchant_trans_id' => $payment->payer_reference,
                    'merchant_prepare_id' => (string) $payment->id,
                    'error' => 0,
                    'error_note' => 'success',
                ],
                Payment::STATUS_SUCCESS, Payment::STATUS_FUNDED => throw new HttpException(400, 'Already paid'),
                Payment::STATUS_CANCELLED, Payment::STATUS_FAILED => throw new HttpException(400, 'Transaction cancelled'),
                default => throw new HttpException(400, 'Transaction error'),
            };
        }

        $payment = Payment::create([
            'provider' => 'click',
            'transaction_id' => $clickTransId,
            'payer_reference' => $merchantTransId,
            'donation_id' => $donationId ?: null,
            'amount' => $amount,
            'status' => Payment::STATUS_PENDING,
            'currency' => config('payments.currency', 'UZS'),
            'external_id' => (string) ($payload['click_paydoc_id'] ?? ''),
            'service_id' => (string) ($payload['service_id'] ?? ''),
            'provider_time_ms' => $this->parseSignTimeToMs($payload['sign_time'] ?? null),
            'provider_create_time' => $this->currentTimestampMs(),
            'provider_perform_time' => 0,
            'provider_cancel_time' => 0,
            'live_mode' => true,
            'payload' => [
                'click_trans_id' => (string) ($payload['click_trans_id'] ?? ''),
                'click_paydoc_id' => (string) ($payload['click_paydoc_id'] ?? ''),
                'click_service_id' => (string) ($payload['service_id'] ?? ''),
                'click_sign_time' => $payload['sign_time'] ?? null,
                'click_sign_string' => $payload['sign_string'] ?? null,
                'merchant_trans_id' => $payload['merchant_trans_id'] ?? null,
            ],
        ]);

        return [
            'click_trans_id' => $payment->transaction_id,
            'merchant_trans_id' => $payment->payer_reference,
            'merchant_prepare_id' => (string) $payment->id,
            'error' => 0,
            'error_note' => 'success',
        ];
    }

    protected function complete(array $payload): array
    {
        $this->validateBasePayload($payload, false);
        $this->validateSignature($payload, false);

        $merchantPrepareId = (string) $payload['merchant_prepare_id'];
        $amount = (float) $payload['amount'];
        $error = (int) ($payload['error'] ?? 0);

        $payment = Payment::query()
            ->where('provider', 'click')
            ->where('id', $merchantPrepareId)
            ->first();

        if (!$payment) {
            throw new HttpException(404, 'Transaction does not exist');
        }

        if (round((float) $payment->amount, 2) !== round($amount, 2)) {
            throw new HttpException(400, 'Incorrect amount');
        }

        if ($payment->status === Payment::STATUS_PENDING) {
            if ($error === 0) {
                $payment->status = Payment::STATUS_SUCCESS;
                $payment->external_id = (string) ($payload['click_paydoc_id'] ?? $payment->external_id);
                $payment->payload = array_merge($payment->payload ?? [], [
                    'click_paydoc_id' => (string) ($payload['click_paydoc_id'] ?? ''),
                    'click_sign_time' => $payload['sign_time'] ?? null,
                    'click_sign_string' => $payload['sign_string'] ?? null,
                ]);
                $payment->provider_perform_time = $this->currentTimestampMs();
                $payment->save();

                if ($payment->donation_id) {
                    Donation::where('id', $payment->donation_id)->update([
                        'status' => 'completed',
                    ]);
                }

                return [
                    'click_trans_id' => $payment->transaction_id,
                    'merchant_trans_id' => $payment->payer_reference,
                    'merchant_confirm_id' => (string) $payment->id,
                    'error' => 0,
                    'error_note' => 'success',
                ];
            }

            $payment->status = Payment::STATUS_CANCELLED;
            $payment->provider_cancel_time = $this->currentTimestampMs();
            $payment->payload = array_merge($payment->payload ?? [], [
                'click_error' => $payload['error'] ?? null,
                'click_error_note' => $payload['error_note'] ?? null,
            ]);
            $payment->save();

            if ($payment->donation_id) {
                Donation::where('id', $payment->donation_id)->update([
                    'status' => 'cancelled',
                ]);
            }

            throw new HttpException(400, 'Transaction cancelled');
        }

        if (in_array($payment->status, [Payment::STATUS_SUCCESS, Payment::STATUS_FUNDED], true)) {
            throw new HttpException(400, 'Already paid');
        }

        throw new HttpException(400, 'Transaction cancelled');
    }

    protected function validateBasePayload(array $payload, bool $isPrepare): void
    {
        $required = [
            'click_trans_id',
            'service_id',
            'click_paydoc_id',
            'sign_time',
            'sign_string',
            'action',
            'amount',
            'merchant_trans_id',
        ];

        if (!$isPrepare) {
            $required[] = 'merchant_prepare_id';
        }

        foreach ($required as $field) {
            if (!array_key_exists($field, $payload)) {
                throw new HttpException(400, "Missing field: {$field}");
            }
        }

        if ((int) $payload['action'] !== ($isPrepare ? self::ACTION_PREPARE : self::ACTION_COMPLETE)) {
            throw new HttpException(400, 'Invalid action type');
        }

        $min = (float) config('payments.click.min_amount', 500);
        $max = (float) config('payments.click.max_amount', 100000000);
        $amount = (float) $payload['amount'];

        if ($amount < $min || $amount > $max) {
            throw new HttpException(400, 'Invalid amount');
        }
    }

    protected function validateSignature(array $payload, bool $isPrepare): void
    {
        if (!config('payments.click.check_signature', true)) {
            return;
        }

        $secret = (string) config('payments.click.secret_key');
        $prepareId = $isPrepare ? '' : (string) ($payload['merchant_prepare_id'] ?? '');

        $sign = md5(
            (string) $payload['click_trans_id'] .
            (string) $payload['service_id'] .
            $secret .
            (string) $payload['merchant_trans_id'] .
            $prepareId .
            (string) $payload['amount'] .
            (string) $payload['action'] .
            (string) $payload['sign_time']
        );

        if ($sign !== (string) $payload['sign_string']) {
            throw new HttpException(400, 'Signature check failed');
        }
    }

    protected function decodeMerchantTransId(string $value): string
    {
        return urldecode($value);
    }

    protected function parseSignTimeToMs(?string $signTime): int
    {
        if (!$signTime) {
            return $this->currentTimestampMs();
        }

        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $signTime);

        return $dt ? ($dt->getTimestamp() * 1000) : $this->currentTimestampMs();
    }

    protected function currentTimestampMs(): int
    {
        return (int) round(microtime(true) * 1000);
    }
}
