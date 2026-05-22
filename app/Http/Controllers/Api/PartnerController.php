<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Support\LocalizedContent;
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
            'data' => PartnerResource::collection($partners->items()),
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
        $validated = $request->validate(array_merge(
            [
                'type' => ['required', 'string', 'in:foundation,ngo,government,medical,media,corporate'],
                'logo_url' => ['nullable', 'string', 'max:2048'],
                'website' => ['nullable', 'string', 'max:500'],
                'is_active' => ['sometimes', 'boolean'],
            ],
            LocalizedContent::adminValidationRules('name', true, 255),
            LocalizedContent::adminValidationRules('description', true)
        ));

        $validated = LocalizedContent::syncLegacyColumns(
            LocalizedContent::prepareAdminLocalized($validated, ['name', 'description']),
            ['name', 'description']
        );
        $validated['is_active'] = $validated['is_active'] ?? true;

        $partner = Partner::create($validated);

        return response()->json([
            'message' => 'Hamkor muvaffaqiyatli yaratildi',
            'data' => new PartnerResource($partner),
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $partner = Partner::query()->findOrFail($id);

        return response()->json([
            'data' => new PartnerResource($partner),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {

        $partner = Partner::query()->findOrFail($id);
        $validated = $request->validate(array_merge(
            [
                'type' => ['required', 'string', 'in:foundation,ngo,government,medical,media,corporate'],
                'logo_url' => ['nullable', 'string', 'max:2048'],
                'website' => ['nullable', 'string', 'max:500'],
                'is_active' => ['sometimes', 'boolean'],
            ],
            LocalizedContent::adminValidationRules('name', true, 255),
            LocalizedContent::adminValidationRules('description', true)
        ));

        $validated = LocalizedContent::syncLegacyColumns(
            LocalizedContent::prepareAdminLocalized($validated, ['name', 'description']),
            ['name', 'description']
        );

        $partner->update($validated);

        return response()->json([
            'message' => 'Partner updated successfully',
            'data' => new PartnerResource($partner->fresh()),
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
