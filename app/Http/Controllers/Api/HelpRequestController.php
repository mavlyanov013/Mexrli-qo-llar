<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHelpRequestRequest;
use App\Http\Resources\HelpRequestResource;
use App\Models\HelpRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HelpRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = HelpRequest::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $items = $query->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'data' => HelpRequestResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function store(StoreHelpRequestRequest $request): JsonResponse
    {
        $payload = $request->validated();

        if (!isset($payload['status'])) {
            $payload['status'] = 'pending';
        }

        $helpRequest = HelpRequest::create($payload);

        return response()->json([
            'message' => 'Yordam so‘rovi yuborildi',
            'data' => new HelpRequestResource($helpRequest),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $helpRequest = HelpRequest::query()->findOrFail($id);

        return response()->json([
            'data' => new HelpRequestResource($helpRequest),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $helpRequest = HelpRequest::query()->findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|string|in:pending,under_review,approved,rejected,more_info_needed',
            'admin_notes' => 'nullable|string',
            'case_id' => 'nullable|integer|exists:case_items,id',
        ]);

        $helpRequest->update($validated);

        return response()->json([
            'message' => 'Help request yangilandi',
            'data' => new HelpRequestResource($helpRequest->fresh()),
        ]);
    }
}
