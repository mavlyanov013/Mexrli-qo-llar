<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Partner::query()->latest();

        if (!$request->boolean('include_inactive', false)) {
            $query->where('is_active', true);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $partners = $query->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'data' => $partners->items(),
            'meta' => [
                'current_page' => $partners->currentPage(),
                'last_page' => $partners->lastPage(),
                'per_page' => $partners->perPage(),
                'total' => $partners->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:foundation,ngo,government,medical,media,corporate'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $partner = Partner::create($validated);

        return response()->json([
            'message' => 'Hamkor muvaffaqiyatli yaratildi',
            'data' => $partner,
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $partner = Partner::query()->findOrFail($id);

        return response()->json([
            'data' => $partner,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {

        $partner = Partner::query()->findOrFail($id);
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:foundation,ngo,government,medical,media,corporate'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $partner->update($validated);

        return response()->json([
            'message' => 'Partner updated successfully',
            'data' => $partner->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $partner = Partner::query()->findOrFail($id);
        $partner->delete();

        return response()->json([
            'message' => 'Partner deleted successfully',
        ]);
    }
}
