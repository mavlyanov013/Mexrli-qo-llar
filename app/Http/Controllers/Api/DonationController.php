<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDonationRequest;
use App\Http\Resources\DonationResource;
use App\Models\Donation;
use Illuminate\Http\JsonResponse;

class DonationController extends Controller
{
    public function index(): JsonResponse
    {
        $donations = Donation::query()
            ->latest()
            ->get();

        return response()->json([
            'data' => DonationResource::collection($donations),
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
        $donation = Donation::create($request->validated());

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
}
