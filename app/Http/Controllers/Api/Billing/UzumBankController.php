<?php

namespace App\Http\Controllers\Api\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\Paynet\PaynetService;
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
            'body' => $request->getContent(),
        ]);

        $xmlResponse = $this->service->handleSoapRequest(
            $request->getContent(),
            $request->ip(),
            'uzumbank'
        );

        Log::info('UZUMBANK SOAP RESPONSE', [
            'response' => $xmlResponse,
        ]);

        return response($xmlResponse, 200, [
            'Content-Type' => 'text/xml; charset=UTF-8',
        ]);
    }
}
