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

    private function forbidUnlessSuperAdmin(): ?JsonResponse
    {
        if (! auth('api')->user()?->hasRole('super_admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return null;
    }

    public function index(Request $request): JsonResponse
    {
        if ($response = $this->forbidUnlessSuperAdmin()) {
            return $response;
        }

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
        if ($response = $this->forbidUnlessSuperAdmin()) {
            return $response;
        }

        return response()->json([
            'message' => 'Payment creation is disabled in admin',
        ], 405);
    }

    public function show(int $id): JsonResponse
    {
        if ($response = $this->forbidUnlessSuperAdmin()) {
            return $response;
        }

        return response()->json([
            'message' => 'Payment view is disabled in admin',
        ], 405);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if ($response = $this->forbidUnlessSuperAdmin()) {
            return $response;
        }

        return response()->json([
            'message' => 'Payment editing is disabled in admin',
        ], 405);
    }

    public function destroy(int $id): JsonResponse
    {
        if ($response = $this->forbidUnlessSuperAdmin()) {
            return $response;
        }

        return response()->json([
            'message' => 'Payment deletion is disabled in admin',
        ], 405);
    }
}
