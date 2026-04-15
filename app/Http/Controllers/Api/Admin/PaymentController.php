<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::query()->latest();

        if ($request->filled('provider')) {
            $query->where('provider', $request->string('provider'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $payments = $query->paginate((int) $request->input('per_page', 20));

        return response()->json([
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
        $payment = Payment::query()->findOrFail($id);

        return response()->json([
            'data' => $payment,
        ]);
    }
}
