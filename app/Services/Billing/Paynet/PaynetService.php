<?php

namespace App\Services\Billing\Paynet;

use App\Models\Donation;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaynetService
{
    public const STATUS_OK = 0;
    public const STATUS_SERVICE_UNAVAILABLE = 100;
    public const STATUS_SYSTEM_ERROR = 102;
    public const STATUS_UNKNOWN_ERROR = 103;
    public const STATUS_TRANSACTION_ALREADY_CREATED = 201;
    public const STATUS_TRANSACTION_NOT_FOUND = 305;
    public const STATUS_TRANSACTION_CANCELED = 202;
    public const STATUS_TRANSACTION_CANNOT_CANCEL = 77;
    public const STATUS_UNKNOWN_USER = 302;
    public const STATUS_INVALID_PASSWORD = 401;
    public const STATUS_MISSING_PARAMETERS = 411;
    public const STATUS_USER_NOT_FOUND = 412;
    public const STATUS_INVALID_AMOUNT = 413;
    public const STATUS_INVALID_DATE = 414;
    public const STATUS_OUTSIDE_THE_SERVICE_ARIA = 502;
    public const STATUS_ACCESS_DENIED = 601;

    protected string $provider = 'paynet';

    public function handleSoapRequest(string $xml, ?string $clientIp = null, string $provider = 'paynet'): string
    {
        $this->provider = $provider;

        Log::info('SOAP HANDLE START', [
            'provider' => $provider,
            'client_ip' => $clientIp,
            'raw_xml' => $xml,
        ]);

        try {
            $document = new \DOMDocument('1.0', 'UTF-8');
            $document->loadXML($xml, LIBXML_NOERROR | LIBXML_NOWARNING);

            $xpath = new \DOMXPath($document);
            $xpath->registerNamespace('soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');
            $xpath->registerNamespace('soap12', 'http://www.w3.org/2003/05/soap-envelope');

            $bodyNode = $xpath->query('/*[local-name()="Envelope"]/*[local-name()="Body"]')->item(0);

            if (!$bodyNode) {
                return $this->faultResponse('Invalid SOAP body');
            }

            $operationNode = null;
            foreach ($bodyNode->childNodes as $child) {
                if ($child instanceof \DOMElement) {
                    $operationNode = $child;
                    break;
                }
            }

            if (!$operationNode) {
                return $this->faultResponse('Invalid SOAP operation');
            }

            $operationName = $operationNode->localName;
            $arguments = $this->xmlNodeToArray($operationNode);

            Log::info('SOAP DEBUG', [
                'provider' => $this->provider,
                'operation_name' => $operationName,
                'operation_node_name' => $operationNode->nodeName,
                'arguments' => $arguments,
            ]);

            return match ($operationName) {
                'PerformTransaction' => $this->performTransactionResponse(
                    $this->performTransaction($arguments, $clientIp)
                ),
                'CheckTransaction' => $this->checkTransactionResponse(
                    $this->checkTransaction($arguments, $clientIp)
                ),
                'CancelTransaction' => $this->cancelTransactionResponse(
                    $this->cancelTransaction($arguments, $clientIp)
                ),
                'GetInformation' => $this->getInformationResponse(
                    $this->getInformation($arguments, $clientIp)
                ),
                'GetStatement' => $this->getStatementResponse(
                    $this->getStatement($arguments, $clientIp)
                ),
                'ChangePassword' => $this->changePasswordResponse(
                    $this->changePassword($arguments, $clientIp)
                ),
                default => $this->faultResponse("Unknown operation: {$operationName}"),
            };
        } catch (Throwable $e) {
            Log::error('SOAP ERROR', [
                'provider' => $this->provider,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->faultResponse('System error');
        }
    }

    public function performTransaction(array $arguments, ?string $clientIp = null): array
    {
        Log::info('SOAP PERFORM TRANSACTION INPUT', [
            'provider' => $this->provider,
            'arguments' => $arguments,
            'client_ip' => $clientIp,
        ]);
        $auth = $this->validateAccess($arguments, $clientIp, true);
        if ($auth !== true) {
            return $this->baseResult($auth, [
                'providerTrnId' => 0,
                'parameters' => [],
            ]);
        }

        $transactionId = $this->getTransactionId($arguments['transactionId'] ?? null);
        if ($transactionId === false) {
            return $this->baseResult(self::STATUS_MISSING_PARAMETERS, [
                'providerTrnId' => 0,
                'parameters' => [],
            ]);
        }

        $transactionTime = $this->parseDateTime($arguments['transactionTime'] ?? null);
        if (!$transactionTime) {
            return $this->baseResult(self::STATUS_INVALID_DATE, [
                'providerTrnId' => 0,
                'parameters' => [],
            ]);
        }

        $amount = $this->normalizeAmount($arguments['amount'] ?? null);
        if ($amount === false) {
            return $this->baseResult(self::STATUS_INVALID_AMOUNT, [
                'providerTrnId' => $transactionId,
                'parameters' => [],
            ]);
        }

        $userData = $this->extractUserData($arguments);

        try {
            DB::beginTransaction();

            $payment = Payment::query()
                ->where('provider', $this->provider)
                ->where('transaction_id', (string) $transactionId)
                ->lockForUpdate()
                ->first();

            if (!$payment && $userData) {
                $payment = Payment::query()
                    ->where('provider', $this->provider)
                    ->where('status', Payment::STATUS_PENDING)
                    ->where(function ($query) use ($userData) {
                        $query->where('payer_reference', (string) $userData)
                            ->orWhere('donation_id', (int) $userData);
                    })
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();
            }

            $donation = null;

            if ($payment?->donation_id) {
                $donation = Donation::query()->find($payment->donation_id);
            }

            if (!$donation && $userData) {
                $donation = Donation::query()->find((int) $userData);
            }

            if ($payment) {
                if ($payment->status === Payment::STATUS_SUCCESS || $payment->status === 'completed') {
                    DB::commit();

                    return $this->baseResult(self::STATUS_TRANSACTION_ALREADY_CREATED, [
                        'providerTrnId' => (int) $payment->transaction_id,
                        'parameters' => [
                            [
                                'paramKey' => 'balance',
                                'paramValue' => (string) $payment->amount,
                            ],
                        ],
                    ]);
                }

                $payment->update([
                    'transaction_id' => (string) $transactionId,
                    'status' => Payment::STATUS_SUCCESS,
                    'payer_reference' => (string) ($userData ?: $payment->payer_reference),
                    'amount' => $amount,
                    'currency' => 'UZS',
                    'provider_time_ms' => $transactionTime->getTimestampMs(),
                    'provider_create_time' => $payment->provider_create_time ?: $this->nowMs(),
                    'provider_perform_time' => $this->nowMs(),
                    'service_id' => (string) $this->serviceId(),
                    'live_mode' => (bool) $this->providerConfig('live_mode', true),
                    'payload' => array_merge($payment->payload ?? [], [
                        'soap_operation' => 'PerformTransaction',
                        'user_data' => $userData,
                        'parameters' => $this->extractParameters($arguments),
                    ]),
                    'raw_information' => json_encode($arguments, JSON_UNESCAPED_UNICODE),
                    'donation_id' => $donation?->id ?? $payment->donation_id,
                ]);
            } else {
                $payment = Payment::query()->create([
                    'provider' => $this->provider,
                    'transaction_id' => (string) $transactionId,
                    'status' => Payment::STATUS_SUCCESS,
                    'payer_reference' => (string) $userData,
                    'amount' => $amount,
                    'currency' => 'UZS',
                    'provider_time_ms' => $transactionTime->getTimestampMs(),
                    'provider_create_time' => $this->nowMs(),
                    'provider_perform_time' => $this->nowMs(),
                    'service_id' => (string) $this->serviceId(),
                    'live_mode' => (bool) $this->providerConfig('live_mode', true),
                    'payload' => [
                        'soap_operation' => 'PerformTransaction',
                        'user_data' => $userData,
                        'parameters' => $this->extractParameters($arguments),
                    ],
                    'raw_information' => json_encode($arguments, JSON_UNESCAPED_UNICODE),
                    'donation_id' => $donation?->id,
                ]);
            }

            if ($donation && $donation->status !== 'completed') {
                $donation->update([
                    'status' => 'completed',
                ]);
            }

            DB::commit();

            return $this->baseResult(self::STATUS_OK, [
                'providerTrnId' => (int) $payment->transaction_id,
                'parameters' => [
                    [
                        'paramKey' => 'balance',
                        'paramValue' => (string) $amount,
                    ],
                ],
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('PERFORM TRANSACTION ERROR', [
                'provider' => $this->provider,
                'message' => $e->getMessage(),
                'arguments' => $arguments,
            ]);

            return $this->baseResult(self::STATUS_SYSTEM_ERROR, [
                'providerTrnId' => (int) $transactionId,
                'parameters' => [],
            ]);
        }
    }

    public function checkTransaction(array $arguments, ?string $clientIp = null): array
    {
        $auth = $this->validateAccess($arguments, $clientIp, true);
        if ($auth !== true) {
            return [
                'providerTrnId' => 0,
                'transactionState' => 0,
                'transactionStateErrorStatus' => 1,
                'transactionStateErrorMsg' => 'Транзакция не существует',
                ...$this->baseResult($auth),
            ];
        }

        $transactionId = $this->getTransactionId($arguments['transactionId'] ?? null);
        if ($transactionId === false) {
            return [
                'providerTrnId' => 0,
                'transactionState' => 0,
                'transactionStateErrorStatus' => 1,
                'transactionStateErrorMsg' => 'Транзакция не существует',
                ...$this->baseResult(self::STATUS_MISSING_PARAMETERS),
            ];
        }

        $payment = Payment::query()
            ->where('provider', $this->provider)
            ->where('transaction_id', (string) $transactionId)
            ->first();

        if (!$payment) {
            return [
                'providerTrnId' => 0,
                'transactionState' => 0,
                'transactionStateErrorStatus' => 1,
                'transactionStateErrorMsg' => 'Транзакция не существует',
                ...$this->baseResult(self::STATUS_TRANSACTION_NOT_FOUND),
            ];
        }

        $state = 0;
        $stateErrorStatus = 1;
        $stateErrorMsg = 'Транзакция не существует';

        if (in_array($payment->status, [Payment::STATUS_SUCCESS, 'completed'], true)) {
            $state = 1;
            $stateErrorStatus = 0;
            $stateErrorMsg = 'Проведено успешно';
        } elseif ($payment->status === Payment::STATUS_CANCELLED) {
            $state = 2;
            $stateErrorStatus = 1;
            $stateErrorMsg = 'Транзакция отменена';
        }

        return [
            'providerTrnId' => (int) $payment->transaction_id,
            'transactionState' => $state,
            'transactionStateErrorStatus' => $stateErrorStatus,
            'transactionStateErrorMsg' => $stateErrorMsg,
            ...$this->baseResult(self::STATUS_OK),
        ];
    }

    public function cancelTransaction(array $arguments, ?string $clientIp = null): array
    {
        $auth = $this->validateAccess($arguments, $clientIp, true);
        if ($auth !== true) {
            return [
                'transactionState' => 0,
                ...$this->baseResult($auth),
            ];
        }

        $transactionId = $this->getTransactionId($arguments['transactionId'] ?? null);
        if ($transactionId === false) {
            return [
                'transactionState' => 0,
                ...$this->baseResult(self::STATUS_MISSING_PARAMETERS),
            ];
        }

        $payment = Payment::query()
            ->where('provider', $this->provider)
            ->where('transaction_id', (string) $transactionId)
            ->first();

        if (!$payment) {
            return [
                'transactionState' => 0,
                ...$this->baseResult(self::STATUS_TRANSACTION_NOT_FOUND),
            ];
        }

        if ($payment->status === Payment::STATUS_SUCCESS) {
            DB::beginTransaction();

            try {
                $payment->update([
                    'status' => Payment::STATUS_CANCELLED,
                    'provider_cancel_time' => $this->nowMs(),
                    'raw_information' => json_encode([
                        'cancel_arguments' => $arguments,
                    ], JSON_UNESCAPED_UNICODE),
                ]);

                if ($payment->donation_id) {
                    $donation = Donation::query()->find($payment->donation_id);
                    if ($donation) {
                        $donation->update([
                            'status' => 'cancelled',
                        ]);
                    }
                }

                DB::commit();

                return [
                    'transactionState' => 2,
                    ...$this->baseResult(self::STATUS_OK),
                ];
            } catch (Throwable $e) {
                DB::rollBack();

                Log::error('CANCEL ERROR', [
                    'provider' => $this->provider,
                    'message' => $e->getMessage(),
                    'arguments' => $arguments,
                ]);

                return [
                    'transactionState' => 0,
                    ...$this->baseResult(self::STATUS_SYSTEM_ERROR),
                ];
            }
        }

        if ($payment->status === Payment::STATUS_CANCELLED) {
            return [
                'transactionState' => 2,
                ...$this->baseResult(self::STATUS_TRANSACTION_CANCELED),
            ];
        }

        return [
            'transactionState' => 0,
            ...$this->baseResult(self::STATUS_TRANSACTION_CANNOT_CANCEL),
        ];
    }

    public function getInformation(array $arguments, ?string $clientIp = null): array
    {
        $auth = $this->validateAccess($arguments, $clientIp, true);
        if ($auth !== true) {
            return [
                'parameters' => [],
                ...$this->baseResult($auth),
            ];
        }

        $parameters = $this->extractParameters($arguments);
        $userKey = $parameters[0]['paramKey'] ?? '';
        $userData = $parameters[0]['paramValue'] ?? '';

        return [
            'parameters' => [
                [
                    'paramKey' => $userKey,
                    'paramValue' => $userData,
                ],
            ],
            ...$this->baseResult(self::STATUS_OK),
        ];
    }

    public function getStatement(array $arguments, ?string $clientIp = null): array
    {
        $auth = $this->validateAccess($arguments, $clientIp, true);
        if ($auth !== true) {
            return [
                'statements' => [],
                ...$this->baseResult($auth),
            ];
        }

        $from = $this->parseDateTime($arguments['dateFrom'] ?? null);
        $to = $this->parseDateTime($arguments['dateTo'] ?? null);

        if (!$from || !$to) {
            return [
                'statements' => [],
                ...$this->baseResult(self::STATUS_INVALID_DATE),
            ];
        }

        $payments = Payment::query()
            ->where('provider', $this->provider)
            ->where('status', Payment::STATUS_SUCCESS)
            ->whereBetween('provider_time_ms', [$from->getTimestampMs(), $to->getTimestampMs()])
            ->orderBy('transaction_id')
            ->get();

        $statements = $payments->map(function (Payment $payment) {
            return [
                'amount' => (int) round($payment->amount * 100),
                'providerTrnId' => (int) $payment->transaction_id,
                'transactionId' => (int) $payment->transaction_id,
                'transactionTime' => $this->formatSoapDateTimeFromMs($payment->provider_time_ms),
            ];
        })->values()->all();

        return [
            'statements' => $statements,
            ...$this->baseResult(self::STATUS_OK),
        ];
    }

    public function changePassword(array $arguments, ?string $clientIp = null): array
    {
        $auth = $this->validateAccess($arguments, $clientIp, false);
        if ($auth !== true) {
            return $this->baseResult($auth);
        }

        $newPassword = $arguments['newPassword'] ?? null;
        if (!is_string($newPassword) || mb_strlen($newPassword) < 7) {
            return $this->baseResult(self::STATUS_INVALID_PASSWORD);
        }

        return $this->baseResult(self::STATUS_OK);
    }

    protected function validateAccess(array $arguments, ?string $clientIp, bool $checkService = true): bool|int
    {
        if (!$this->isAllowedIp($clientIp)) {
            return self::STATUS_ACCESS_DENIED;
        }

        $username = (string) ($arguments['username'] ?? '');
        $password = (string) ($arguments['password'] ?? '');

        if (!$this->validateUser($username, $password)) {
            return self::STATUS_ACCESS_DENIED;
        }

        if ($checkService) {
            $serviceId = (int) ($arguments['serviceId'] ?? 0);
            if ($serviceId !== $this->serviceId()) {
                return self::STATUS_OUTSIDE_THE_SERVICE_ARIA;
            }
        }

        return true;
    }

    protected function validateUser(string $username, string $password): bool
    {
        $configUsername = (string) $this->providerConfig('username');
        $passwordHash = (string) $this->providerConfig('password_hash');

        if ($username !== $configUsername) {
            return false;
        }

        if (!$passwordHash) {
            return false;
        }

        return Hash::check($password, $passwordHash);
    }

    protected function isAllowedIp(?string $clientIp): bool
    {
        $allowedIps = $this->providerConfig('allowed_ips', []);
        $liveMode = (bool) $this->providerConfig('live_mode', true);
        $enabled = (bool) $this->providerConfig('enabled', true);

        if (!$enabled) {
            return false;
        }

        if (!$liveMode) {
            return true;
        }

        if (empty($allowedIps)) {
            return true;
        }

        if (!$clientIp) {
            return false;
        }

        foreach ($allowedIps as $allowedIp) {
            if ($this->ipInRange($clientIp, $allowedIp)) {
                return true;
            }
        }

        return false;
    }

    protected function ipInRange(string $ip, string $range): bool
    {
        if (!str_contains($range, '/')) {
            $range .= '/32';
        }

        [$rangeIp, $netmask] = explode('/', $range, 2);

        $rangeDecimal = ip2long($rangeIp);
        $ipDecimal = ip2long($ip);
        $wildcardDecimal = pow(2, (32 - (int) $netmask)) - 1;
        $netmaskDecimal = ~$wildcardDecimal;

        return (($ipDecimal & $netmaskDecimal) === ($rangeDecimal & $netmaskDecimal));
    }

    protected function getTransactionId(mixed $transactionId): int|false
    {
        if (is_numeric($transactionId) && (int) $transactionId > 0) {
            return (int) $transactionId;
        }

        return false;
    }

    protected function normalizeAmount(mixed $amount): float|false
    {
        if (!is_numeric($amount)) {
            return false;
        }

        $amount = (int) $amount;
        if ($amount <= 0) {
            return false;
        }

        $normalized = $amount / 100;

        $min = (float) $this->providerConfig('min_amount', 500);
        $max = (float) $this->providerConfig('max_amount', 100000000);

        if ($normalized < $min || $normalized > $max) {
            return false;
        }

        return $normalized;
    }

    protected function parseDateTime(?string $value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        if (mb_strlen($value) > 32 && str_contains($value, '+')) {
            $parts = explode('+', $value, 2);
            $value = substr($parts[0], 0, 26) . '+' . $parts[1];
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    protected function extractParameters(array $arguments): array
    {
        $parameters = $arguments['parameters'] ?? [];

        if (isset($parameters['paramKey'])) {
            return [[
                'paramKey' => (string) ($parameters['paramKey'] ?? ''),
                'paramValue' => (string) ($parameters['paramValue'] ?? ''),
            ]];
        }

        if (!is_array($parameters)) {
            return [];
        }

        return collect($parameters)->map(function ($item) {
            return [
                'paramKey' => (string) ($item['paramKey'] ?? ''),
                'paramValue' => (string) ($item['paramValue'] ?? ''),
            ];
        })->all();
    }

    protected function extractUserData(array $arguments): ?string
    {
        $parameters = $this->extractParameters($arguments);

        return $parameters[0]['paramValue'] ?? null;
    }

    protected function xmlNodeToArray(?\DOMNode $node): array
    {
        if (!$node) {
            return [];
        }

        $result = [];

        foreach ($node->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }

            $key = $child->localName;

            if ($child->childNodes->length === 1 && $child->firstChild?->nodeType === XML_TEXT_NODE) {
                $result[$key] = $child->textContent;
                continue;
            }

            $value = $this->xmlNodeToArray($child);

            if (isset($result[$key])) {
                if (!array_is_list($result[$key])) {
                    $result[$key] = [$result[$key]];
                }
                $result[$key][] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    protected function providerConfig(string $key, mixed $default = null): mixed
    {
        return config("payments.{$this->provider}.{$key}", $default);
    }

    protected function serviceId(): int
    {
        return (int) $this->providerConfig('service_id', 1);
    }

    protected function baseResult(int $status, array $extra = []): array
    {
        return array_merge([
            'errorMsg' => $this->getMessage($status),
            'status' => $status,
            'timeStamp' => now()->toIso8601String(),
        ], $extra);
    }

    protected function getMessage(int $status): string
    {
        return [
            self::STATUS_OK => 'Ok',
            self::STATUS_SERVICE_UNAVAILABLE => 'Услуга временно не поддерживается',
            self::STATUS_SYSTEM_ERROR => 'Системная ошибка',
            self::STATUS_UNKNOWN_ERROR => 'Неизвестная ошибка',
            self::STATUS_TRANSACTION_ALREADY_CREATED => 'Транзакция уже существует',
            self::STATUS_TRANSACTION_NOT_FOUND => 'Транзакция не найден',
            self::STATUS_TRANSACTION_CANCELED => 'Транзакция уже отменена',
            self::STATUS_TRANSACTION_CANNOT_CANCEL => 'Недостаточно средств на счету клиента для отмены платежа ',
            self::STATUS_UNKNOWN_USER => 'Пользователь не найден',
            self::STATUS_INVALID_PASSWORD => 'Пароль должен содержать не менее 7 символов',
            self::STATUS_MISSING_PARAMETERS => 'Не задан один или несколько обязательных параметров',
            self::STATUS_USER_NOT_FOUND => 'Пользователь не найден',
            self::STATUS_INVALID_AMOUNT => 'Неверная сумма',
            self::STATUS_INVALID_DATE => 'Неверный формат даты и времени',
            self::STATUS_OUTSIDE_THE_SERVICE_ARIA => 'Клиент вне зоны обслуживания провайдера',
            self::STATUS_ACCESS_DENIED => 'Доступ запрещен',
        ][$status] ?? 'Неизвестная ошибка';
    }

    protected function nowMs(): int
    {
        return (int) round(microtime(true) * 1000);
    }

    protected function formatSoapDateTimeFromMs(?int $ms): ?string
    {
        if (!$ms) {
            return null;
        }

        return Carbon::createFromTimestampMs($ms)->toIso8601String();
    }

    protected function envelope(string $body): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tns="http://uws.provider.com/">
    <soap:Body>
        {$body}
    </soap:Body>
</soap:Envelope>
XML;
    }

    protected function faultResponse(string $message): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <soap:Fault>
            <faultcode>soap:Server</faultcode>
            <faultstring>{$this->escape($message)}</faultstring>
        </soap:Fault>
    </soap:Body>
</soap:Envelope>
XML;
    }

    protected function performTransactionResponse(array $data): string
    {
        $paramsXml = $this->parametersXml($data['parameters'] ?? []);

        return $this->envelope(
            '<tns:PerformTransactionResponse>' .
            '<tns:result>' .
            '<tns:errorMsg>' . $this->escape($data['errorMsg']) . '</tns:errorMsg>' .
            '<tns:status>' . (int) $data['status'] . '</tns:status>' .
            '<tns:timeStamp>' . $this->escape($data['timeStamp']) . '</tns:timeStamp>' .
            $paramsXml .
            '<tns:providerTrnId>' . (int) ($data['providerTrnId'] ?? 0) . '</tns:providerTrnId>' .
            '</tns:result>' .
            '</tns:PerformTransactionResponse>'
        );
    }

    protected function checkTransactionResponse(array $data): string
    {
        return $this->envelope(
            '<tns:CheckTransactionResponse>' .
            '<tns:result>' .
            '<tns:errorMsg>' . $this->escape($data['errorMsg']) . '</tns:errorMsg>' .
            '<tns:status>' . (int) $data['status'] . '</tns:status>' .
            '<tns:timeStamp>' . $this->escape($data['timeStamp']) . '</tns:timeStamp>' .
            '<tns:providerTrnId>' . (int) ($data['providerTrnId'] ?? 0) . '</tns:providerTrnId>' .
            '<tns:transactionState>' . (int) ($data['transactionState'] ?? 0) . '</tns:transactionState>' .
            '<tns:transactionStateErrorStatus>' . (int) ($data['transactionStateErrorStatus'] ?? 1) . '</tns:transactionStateErrorStatus>' .
            '<tns:transactionStateErrorMsg>' . $this->escape($data['transactionStateErrorMsg'] ?? '') . '</tns:transactionStateErrorMsg>' .
            '</tns:result>' .
            '</tns:CheckTransactionResponse>'
        );
    }

    protected function cancelTransactionResponse(array $data): string
    {
        return $this->envelope(
            '<tns:CancelTransactionResponse>' .
            '<tns:result>' .
            '<tns:errorMsg>' . $this->escape($data['errorMsg']) . '</tns:errorMsg>' .
            '<tns:status>' . (int) $data['status'] . '</tns:status>' .
            '<tns:timeStamp>' . $this->escape($data['timeStamp']) . '</tns:timeStamp>' .
            '<tns:transactionState>' . (int) ($data['transactionState'] ?? 0) . '</tns:transactionState>' .
            '</tns:result>' .
            '</tns:CancelTransactionResponse>'
        );
    }

    protected function getInformationResponse(array $data): string
    {
        $paramsXml = $this->parametersXml($data['parameters'] ?? []);

        return $this->envelope(
            '<tns:GetInformationResponse>' .
            '<tns:result>' .
            '<tns:errorMsg>' . $this->escape($data['errorMsg']) . '</tns:errorMsg>' .
            '<tns:status>' . (int) $data['status'] . '</tns:status>' .
            '<tns:timeStamp>' . $this->escape($data['timeStamp']) . '</tns:timeStamp>' .
            $paramsXml .
            '</tns:result>' .
            '</tns:GetInformationResponse>'
        );
    }

    protected function getStatementResponse(array $data): string
    {
        $statementsXml = '';

        foreach (($data['statements'] ?? []) as $statement) {
            $statementsXml .=
                '<tns:statements>' .
                '<tns:amount>' . (int) ($statement['amount'] ?? 0) . '</tns:amount>' .
                '<tns:providerTrnId>' . (int) ($statement['providerTrnId'] ?? 0) . '</tns:providerTrnId>' .
                '<tns:transactionId>' . (int) ($statement['transactionId'] ?? 0) . '</tns:transactionId>' .
                '<tns:transactionTime>' . $this->escape((string) ($statement['transactionTime'] ?? now()->toIso8601String())) . '</tns:transactionTime>' .
                '</tns:statements>';
        }

        return $this->envelope(
            '<tns:GetStatementResponse>' .
            '<tns:result>' .
            '<tns:errorMsg>' . $this->escape($data['errorMsg']) . '</tns:errorMsg>' .
            '<tns:status>' . (int) $data['status'] . '</tns:status>' .
            '<tns:timeStamp>' . $this->escape($data['timeStamp']) . '</tns:timeStamp>' .
            $statementsXml .
            '</tns:result>' .
            '</tns:GetStatementResponse>'
        );
    }

    protected function changePasswordResponse(array $data): string
    {
        return $this->envelope(
            '<tns:ChangePasswordResponse>' .
            '<tns:result>' .
            '<tns:errorMsg>' . $this->escape($data['errorMsg']) . '</tns:errorMsg>' .
            '<tns:status>' . (int) $data['status'] . '</tns:status>' .
            '<tns:timeStamp>' . $this->escape($data['timeStamp']) . '</tns:timeStamp>' .
            '</tns:result>' .
            '</tns:ChangePasswordResponse>'
        );
    }

    protected function parametersXml(array $parameters): string
    {
        $xml = '';

        foreach ($parameters as $parameter) {
            $xml .=
                '<tns:parameters>' .
                '<tns:paramKey>' . $this->escape((string) ($parameter['paramKey'] ?? '')) . '</tns:paramKey>' .
                '<tns:paramValue>' . $this->escape((string) ($parameter['paramValue'] ?? '')) . '</tns:paramValue>' .
                '</tns:parameters>';
        }

        return $xml;
    }

    protected function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
