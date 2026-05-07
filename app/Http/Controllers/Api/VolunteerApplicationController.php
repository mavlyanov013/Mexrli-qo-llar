<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVolunteerApplicationRequest;
use App\Http\Resources\VolunteerApplicationResource;
use App\Models\VolunteerApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VolunteerApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = VolunteerApplication::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $items = $query->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'data' => VolunteerApplicationResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function store(StoreVolunteerApplicationRequest $request): JsonResponse
    {
        $payload = $request->validated();

        if (!isset($payload['status'])) {
            $payload['status'] = 'new';
        }

        $application = VolunteerApplication::create($payload);

        return response()->json([
            'message' => 'Volunteer ariza yuborildi',
            'data' => new VolunteerApplicationResource($application),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $application = VolunteerApplication::query()->findOrFail($id);

        return response()->json([
            'data' => new VolunteerApplicationResource($application),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $application = VolunteerApplication::query()->findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'phone' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'role_interest' => 'sometimes|string|max:100',
            'message' => 'nullable|string',
            'experience' => 'nullable|string',
            'motivation' => 'nullable|string',
            'availability' => 'nullable|string|max:100',
            'status' => 'sometimes|string|in:new,reviewed,accepted,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        $application->update($validated);

        return response()->json([
            'message' => 'Volunteer application yangilandi',
            'data' => new VolunteerApplicationResource($application->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $application = VolunteerApplication::query()->findOrFail($id);
        $application->delete();

        return response()->json([
            'message' => 'Volunteer application deleted',
        ]);
    }
}
