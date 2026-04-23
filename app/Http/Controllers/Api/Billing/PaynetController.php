<?php

namespace App\Http\Controllers\Api\Billing;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Billing\Paynet\PaynetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PaynetController extends Controller
{
    public function __construct(
        protected PaynetService $paynetService
    ) {}

    public function handle(Request $request): Response
{
    Log::info('PAYNET SOAP REQUEST', [
        'ip' => $request->ip(),
        'method' => $request->method(),
        'content_type' => $request->header('Content-Type'),
        'content_length' => strlen((string) $request->getContent()),
    ]);

    $body = trim((string) $request->getContent());

    if ($request->isMethod('get') || $body === '') {
        return response('Paynet endpoint is alive', 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    $xmlResponse = $this->paynetService->handleSoapRequest($body, $request->ip());

    return response($xmlResponse, 200, [
        'Content-Type' => 'text/xml; charset=UTF-8',
    ]);
}

    public function status(Payment $payment): JsonResponse
    {
        abort_unless($payment->provider === 'paynet', 404);

        return response()->json([
            'data' => [
                'id' => $payment->id,
                'provider' => $payment->provider,
                'transaction_id' => $payment->transaction_id,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'donation_id' => $payment->donation_id,
            ],
        ]);
    }
}
