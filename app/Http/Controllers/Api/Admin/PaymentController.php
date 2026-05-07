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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:paycom,click,paynet,uzumbank,cash'],
            'status' => ['required', 'string', 'in:pending,success,failed,cancelled,completed'],
            'transaction_id' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'donation_id' => ['nullable', 'integer', 'exists:donations,id'],
        ]);

        $payment = \App\Models\Payment::query()->create($validated);

        return response()->json([
            'message' => 'Payment created successfully',
            'data' => $payment,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $payment = $this->paymentService->findOrFail($id);

        return response()->json([
            'message' => 'Payment fetched successfully',
            'data' => $payment,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $payment = $this->paymentService->findOrFail($id);
        $validated = $request->validate([
            'provider' => ['sometimes', 'string', 'in:paycom,click,paynet,uzumbank,cash'],
            'status' => ['sometimes', 'string', 'in:pending,success,failed,cancelled,completed'],
            'transaction_id' => ['sometimes', 'string', 'max:255'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'max:10'],
        ]);

        $updated = $this->paymentService->update($payment, $validated);

        return response()->json([
            'message' => 'Payment updated successfully',
            'data' => $updated,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $payment = $this->paymentService->findOrFail($id);
        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully',
        ]);
    }
}
