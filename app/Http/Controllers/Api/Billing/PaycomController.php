<?php

namespace App\Http\Controllers\Api\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\Paycom\PaycomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PaycomController extends Controller
{
    public function __construct(
        protected PaycomService $service
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $login = $request->getUser();
        $password = $request->getPassword();
        $payload = $request->json()->all();

        if (!$this->service->checkAuth($login, $password)) {
            return response()->json([
                'error' => [
                    'code' => -32504,
                    'message' => 'Unauthorized',
                ],
                'result' => null,
                'id' => $payload['id'] ?? null,
            ], 200);
        }

        \Illuminate\Support\Facades\Log::info('PAYCOM PAYLOAD', [
            'payload' => $payload,
            'raw' => $request->getContent(),
        ]);

        try {
            $result = $this->service->handle($payload);

            return response()->json([
                'error' => null,
                'result' => $result,
                'id' => $payload['id'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => [
                    'code' => $e->getCode() ?: -32400,
                    'message' => $e->getMessage(),
                ],
                'result' => null,
                'id' => $payload['id'] ?? null,
            ], 200);
        }
    }
}
