<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDonationRequest;
use App\Http\Resources\DonationResource;
use App\Models\Donation;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DonationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Donation::query()
            ->with('payment') // 🔥 provider olish uchun MUHIM
            ->latest();

        // 🔎 STATUS FILTER
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        // 🔎 TYPE FILTER
        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        // 🔎 SEARCH FILTER
        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function ($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                    ->orWhere('donor_phone', 'like', "%{$search}%");
            });
        }

        // 📦 FINAL PAGINATION (FAKAT 1 MARTA)
        $donations = $query->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'message' => 'Donations fetched successfully',
            'data' => DonationResource::collection($donations),
            'meta' => [
                'current_page' => $donations->currentPage(),
                'last_page' => $donations->lastPage(),
                'per_page' => $donations->perPage(),
                'total' => $donations->total(),
            ],
        ]);
    }
    public function publicIndex(): JsonResponse
    {
        $donations = Donation::query()
            ->where('status', 'completed')
            ->latest()
            ->get();

        return response()->json([
            'data' => DonationResource::collection($donations),
        ]);
    }
    public function store(StoreDonationRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $isAdminRoute = $request->is('api/v1/admin/*');
        $isManualCash = $isAdminRoute && ($request->boolean('is_manual_cash') || ($payload['type'] ?? null) === 'manual');

        if ($isManualCash) {
            $payload['type'] = 'manual';
            $payload['status'] = 'completed';
            $payload['service_type'] = 'cash';
            $payload['message'] = $payload['note'] ?? ($payload['message'] ?? null);
        }

        unset($payload['is_manual_cash'], $payload['note']);

        $donation = Donation::create($payload);

        if ($isManualCash) {
            Payment::query()->create([
                'provider' => 'cash',
                'transaction_id' => 'CASH-' . strtoupper(Str::random(12)),
                'status' => 'completed',
                'category' => 'manual',
                'payer_reference' => (string) $donation->id,
                'amount' => $donation->amount,
                'currency' => $donation->currency ?: 'UZS',
                'donation_id' => $donation->id,
                'live_mode' => false,
                'type' => 'required|in:online,cash',
            ]);
        }

        return response()->json([
            'message' => 'Donation muvaffaqiyatli qabul qilindi',
            'data' => new DonationResource($donation),
        ], 201);
    }
    public function live(): JsonResponse
    {
        $donations = Donation::query()
            ->where('status', 'completed')
            ->latest()
            ->limit(15)
            ->get();

        return response()->json([
            'data' => $donations,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $donation = Donation::query()->findOrFail($id);

        return response()->json([
            'data' => new DonationResource($donation),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $donation = Donation::query()->findOrFail($id);
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'message' => ['nullable', 'string'],
        ]);
        $donation->update($validated);

        return response()->json([
            'message' => 'Donation updated successfully',
            'data' => new DonationResource($donation->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $donation = Donation::query()->findOrFail($id);
        $donation->delete();

        return response()->json([
            'message' => 'Donation deleted successfully',
        ]);
    }
}
