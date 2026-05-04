<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $payments = $this->paymentService->list($request);

        return response()->json([
            'message' => 'Payments fetched successfully',
            'data' => $payments->items(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $payment = $this->paymentService->findOrFail($id);

        return response()->json([
            'message' => 'Payment fetched successfully',
            'data' => $payment,
        ]);
    }
}
