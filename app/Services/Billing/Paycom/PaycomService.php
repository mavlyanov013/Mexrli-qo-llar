<?php

namespace App\Services\Billing\Paycom;

use App\Models\Donation;
use App\Models\Payment;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaycomService
{
    public const METHOD_CHECK_PERFORM = 'CheckPerformTransaction';
    public const METHOD_CREATE = 'CreateTransaction';
    public const METHOD_PERFORM = 'PerformTransaction';
    public const METHOD_CANCEL = 'CancelTransaction';
    public const METHOD_CHECK = 'CheckTransaction';
    public const METHOD_STATEMENT = 'GetStatement';

    public const TRANSACTION_STATE_PENDING = 1;
    public const TRANSACTION_STATE_SUCCESS = 2;
    public const TRANSACTION_STATE_CANCELLED_BEFORE_PERFORM = -1;
    public const TRANSACTION_STATE_CANCELLED_AFTER_PERFORM = -2;

    public function handle(array $payload): array
    {
        if (!isset($payload['method'], $payload['params'])) {
            throw new HttpException(400, 'Invalid request');
        }

        return match ($payload['method']) {
            self::METHOD_CHECK_PERFORM => $this->checkPerformTransaction($payload),
            self::METHOD_CREATE => $this->createTransaction($payload),
            self::METHOD_PERFORM => $this->performTransaction($payload),
            self::METHOD_CANCEL => $this->cancelTransaction($payload),
            self::METHOD_CHECK => $this->checkTransaction($payload),
            self::METHOD_STATEMENT => $this->getStatement($payload),
            default => throw new HttpException(400, 'Method not found'),
        };
    }

    public function checkAuth(?string $login, ?string $password): bool
    {
        $configLogin = config('payments.paycom.api_login');
        $configPassword = config('payments.paycom.api_password');

        return $login === $configLogin && $password === $configPassword;
    }

    protected function checkPerformTransaction(array $payload): array
    {
        $params = $payload['params'];
        $this->validateCheckPerformParams($params);

        return ['allow' => true];
    }

    protected function createTransaction(array $payload): array
    {
        $params = $payload['params'];
        $requestId = $payload['id'] ?? null;

        $this->validateCreateParams($params);

        $providerTransactionId = (string) $params['id'];
        $donationId = isset($params['account']['user_data'])
            ? (int) $params['account']['user_data']
            : null;

        $payment = Payment::query()
            ->where('provider', 'paycom')
            ->where('transaction_id', $providerTransactionId)
            ->first();

        if (!$payment && $donationId) {
            $payment = Payment::query()
                ->where('provider', 'paycom')
                ->where('donation_id', $donationId)
                ->where('status', Payment::STATUS_PENDING)
                ->latest('id')
                ->first();
        }

        if ($payment) {
            $payment->transaction_id = $providerTransactionId;
            $payment->payer_reference = (string) ($donationId ?: $payment->payer_reference);
            $payment->provider_time_ms = (int) $params['time'];
            $payment->provider_create_time = $payment->provider_create_time ?: $this->currentTimestampMs();
            $payment->payload = array_merge($payment->payload ?? [], [
                'request_id' => $requestId,
                'request_time' => (int) $params['time'],
                'account' => $params['account'] ?? [],
            ]);
            $payment->save();

            return [
                'create_time' => $payment->provider_create_time,
                'transaction' => (string) $payment->id,
                'state' => $payment->getPaycomState(),
                'receivers' => null,
            ];
        }

        $payment = Payment::create([
            'transaction_id' => $providerTransactionId,
            'provider' => 'paycom',
            'payer_reference' => $donationId ? (string) $donationId : null,
            'donation_id' => $donationId,
            'amount' => $this->normalizeAmountFromTiyin($params['amount']),
            'status' => Payment::STATUS_PENDING,
            'provider_time_ms' => (int) $params['time'],
            'provider_create_time' => $this->currentTimestampMs(),
            'provider_perform_time' => 0,
            'provider_cancel_time' => 0,
            'payload' => [
                'request_id' => $requestId,
                'request_time' => (int) $params['time'],
                'account' => $params['account'] ?? [],
            ],
            'currency' => config('payments.currency', 'UZS'),
            'live_mode' => true,
        ]);

        return [
            'create_time' => $payment->provider_create_time,
            'transaction' => (string) $payment->id,
            'state' => $payment->getPaycomState(),
            'receivers' => null,
        ];
    }

    protected function findPaycomPayment(string $id): ?Payment
    {
        return Payment::query()
            ->where('provider', 'paycom')
            ->where(function ($query) use ($id) {
                $query->where('transaction_id', $id)
                    ->orWhere('id', $id);
            })
            ->first();
    }

    protected function performTransaction(array $payload): array
    {
        $params = $payload['params'];

        if (empty($params['id'])) {
            throw new HttpException(400, 'Transaction id required');
        }

        $payment = $this->findPaycomPayment((string) $params['id']);

        if (!$payment) {
            throw new HttpException(404, 'Transaction not found');
        }

        if ($payment->getPaycomState() === self::TRANSACTION_STATE_PENDING) {
            $payment->status = Payment::STATUS_SUCCESS;
            $payment->provider_perform_time = $this->currentTimestampMs();
            $payment->save();

            if ($payment->donation_id) {
                Donation::where('id', $payment->donation_id)->update([
                    'status' => 'completed',
                ]);
            }
        }

        return [
            'transaction' => (string) $payment->id,
            'perform_time' => $payment->provider_perform_time,
            'state' => $payment->getPaycomState(),
        ];
    }

    protected function cancelTransaction(array $payload): array
    {
        $params = $payload['params'];

        if (empty($params['id'])) {
            throw new HttpException(400, 'Transaction id required');
        }

        $payment = $this->findPaycomPayment((string) $params['id']);

        if (!$payment) {
            throw new HttpException(404, 'Transaction not found');
        }

        $payment->status = Payment::STATUS_CANCELLED;
        $payment->provider_cancel_time = $this->currentTimestampMs();
        $payment->save();

        if ($payment->donation_id) {
            Donation::where('id', $payment->donation_id)->update([
                'status' => 'cancelled',
            ]);
        }

        return [
            'transaction' => (string) $payment->id,
            'cancel_time' => $payment->provider_cancel_time,
            'state' => $payment->getPaycomState(),
        ];
    }

    protected function checkTransaction(array $payload): array
    {
        $params = $payload['params'];

        if (empty($params['id'])) {
            throw new HttpException(400, 'Transaction id required');
        }

        $payment = $this->findPaycomPayment((string) $params['id']);

        if (!$payment) {
            throw new HttpException(404, 'Transaction not found');
        }

        return [
            'create_time' => $payment->provider_create_time,
            'perform_time' => $payment->provider_perform_time,
            'cancel_time' => $payment->provider_cancel_time,
            'transaction' => (string) $payment->id,
            'state' => $payment->getPaycomState(),
            'reason' => $payment->payload['reason'] ?? null,
        ];
    }

    protected function getStatement(array $payload): array
    {
        $params = $payload['params'];

        if (!isset($params['from'], $params['to'])) {
            throw new HttpException(400, 'from and to required');
        }

        $payments = Payment::query()
            ->where('provider', 'paycom')
            ->where('provider_create_time', '>=', (int) $params['from'])
            ->where('provider_create_time', '<=', (int) $params['to'])
            ->orderBy('provider_create_time')
            ->get();

        return [
            'transactions' => $payments->map(function (Payment $payment) {
                return [
                    'id' => $payment->transaction_id,
                    'time' => $payment->payload['request_time'] ?? $payment->provider_time_ms,
                    'amount' => (int) round($payment->amount * 100),
                    'account' => [
                        'user_data' => $payment->payer_reference,
                    ],
                    'create_time' => $payment->provider_create_time,
                    'perform_time' => $payment->provider_perform_time,
                    'cancel_time' => $payment->provider_cancel_time,
                    'transaction' => (string) $payment->id,
                    'state' => $payment->getPaycomState(),
                    'reason' => $payment->payload['reason'] ?? null,
                    'receivers' => null,
                ];
            })->values()->all(),
        ];
    }

    protected function validateCheckPerformParams(array $params): void
    {
        if (!isset($params['amount'], $params['account']['user_data'])) {
            throw new HttpException(400, 'Invalid params');
        }

        $amount = $this->normalizeAmountFromTiyin($params['amount']);
        $min = (float) config('payments.paycom.min_amount', 100);
        $max = (float) config('payments.paycom.max_amount', 100000000);

        if ($amount < $min || $amount > $max) {
            throw new HttpException(400, 'Invalid amount');
        }
    }

    protected function validateCreateParams(array $params): void
    {
        if (!isset($params['id'], $params['time'], $params['amount'], $params['account']['user_data'])) {
            throw new HttpException(400, 'Invalid params');
        }

        $this->validateCheckPerformParams($params);
    }

    protected function normalizeAmountFromTiyin(int|float|string $amount): float
    {
        return ((float) $amount) / 100;
    }

    protected function currentTimestampMs(): int
    {
        return (int) round(microtime(true) * 1000);
    }
}
