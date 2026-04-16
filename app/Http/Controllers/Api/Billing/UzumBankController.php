<?php

namespace App\Http\Controllers\Api\Billing;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Billing\Paynet\PaynetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class UzumBankController extends Controller
{
    public function __construct(
        protected PaynetService $service
    ) {
    }

    public function handle(Request $request): Response
    {
        Log::info('UZUMBANK SOAP REQUEST', [
            'ip' => $request->ip(),
            'content_type' => $request->header('Content-Type'),
        ]);

        if (app()->environment('local', 'staging')) {
            Log::debug('UZUMBANK SOAP REQUEST RAW', [
                'body' => $request->getContent(),
            ]);
        }

        $xmlResponse = $this->service->handleSoapRequest(
            $request->getContent(),
            $request->ip(),
            'uzumbank'
        );

        if (app()->environment('local', 'staging')) {
            Log::debug('UZUMBANK SOAP RESPONSE RAW', [
                'response' => $xmlResponse,
            ]);
        }

        return response($xmlResponse, 200, [
            'Content-Type' => 'text/xml; charset=UTF-8',
        ]);
    }

    public function status(Payment $payment): JsonResponse
    {
        abort_unless($payment->provider === 'uzumbank', 404);

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
