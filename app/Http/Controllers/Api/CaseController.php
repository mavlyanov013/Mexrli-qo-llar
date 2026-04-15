<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CaseResource;
use App\Models\CaseItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CaseItem::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->boolean('public_only', false)) {
            $query->where('status', '!=', 'closed');
        }

        $cases = $query->paginate((int) $request->input('per_page', 12));

        return response()->json([
            'data' => CaseResource::collection($cases->items()),
            'meta' => [
                'current_page' => $cases->currentPage(),
                'last_page' => $cases->lastPage(),
                'per_page' => $cases->perPage(),
                'total' => $cases->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'story' => 'nullable|string',
            'short_description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'urgency' => 'nullable|string|max:50',
            'goal_amount' => 'nullable|numeric|min:0',
            'raised_amount' => 'nullable|numeric|min:0',
            'is_urgent' => 'sometimes|boolean',
            'medical_documents' => 'nullable|array',
            'medical_documents.*' => 'string',
        ]);

        $case = CaseItem::create($validated);

        return response()->json([
            'message' => 'Case yaratildi',
            'data' => new CaseResource($case),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $case = CaseItem::query()->findOrFail($id);

        return response()->json([
            'data' => new CaseResource($case),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $case = CaseItem::query()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'location' => 'nullable|string|max:255',
            'story' => 'nullable|string',
            'short_description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'urgency' => 'nullable|string|max:50',
            'goal_amount' => 'nullable|numeric|min:0',
            'raised_amount' => 'nullable|numeric|min:0',
            'is_urgent' => 'sometimes|boolean',
            'medical_documents' => 'nullable|array',
            'medical_documents.*' => 'string',
        ]);

        $case->update($validated);

        return response()->json([
            'message' => 'Case yangilandi',
            'data' => new CaseResource($case->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $case = CaseItem::query()->findOrFail($id);
        $case->delete();

        return response()->json([
            'message' => 'Case o‘chirildi',
        ]);
    }
}
