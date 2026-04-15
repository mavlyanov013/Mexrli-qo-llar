<?php

namespace App\Http\Controllers\Api\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\Click\ClickService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ClickController extends Controller
{
    public function __construct(
        protected ClickService $service
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        try {
            $result = $this->service->handle($payload);

            return response()->json($result);
        } catch (Throwable $e) {
            return response()->json([
                'error' => $this->mapErrorCode($e->getMessage()),
                'error_note' => $e->getMessage(),
            ]);
        }
    }

    protected function mapErrorCode(string $message): int
    {
        return match ($message) {
            'Signature check failed' => -1,
            'Incorrect amount' => -2,
            'Action not found' => -3,
            'Already paid' => -4,
            'Transaction does not exist' => -6,
            'Transaction cancelled' => -9,
            default => -8,
        };
    }
}
