<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CaseResource;
use App\Models\CaseItem;
use App\Support\LocalizedContent;
use App\Support\CasePhotos;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CaseItem::query()
            ->withCount('donations')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->boolean('public_only', false)) {
            $query->where('status', '!=', 'closed');
        }

        if ($request->boolean('needs_funding', false)) {
            $query->where('status', 'active')
                ->where('goal_amount', '>', 0)
                ->whereColumn('raised_amount', '<', 'goal_amount');
        }

        $cases = $query->paginate((int) $request->input('per_page', 12));

        return response()->json([
            'data' => CaseResource::collection($cases),
            'meta' => [
                'current_page' => $cases->currentPage(),
                'last_page' => $cases->lastPage(),
                'per_page' => $cases->perPage(),
                'total' => $cases->total(),
            ],
        ]);
    }

    public function active(): JsonResponse
    {
        $cases = CaseItem::query()
            ->where('status', 'active')
            ->where('goal_amount', '>', 0)
            ->whereColumn('raised_amount', '<', 'goal_amount')
            ->orderByDesc('is_urgent')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'data' => CaseResource::collection($cases),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(array_merge(
            [
                'phone' => 'nullable|string|max:50',
                'category' => 'nullable|string|max:100',
                'source' => 'nullable|string|max:100',
                'created_from_request_id' => 'nullable|integer|exists:help_requests,id',
                'status' => 'nullable|string|in:new,draft,active,paused,completed,closed',
                'urgency' => 'nullable|string|max:50',
                'goal_amount' => 'nullable|numeric|min:0',
                'raised_amount' => 'nullable|numeric|min:0',
                'is_urgent' => 'sometimes|boolean',
                'medical_documents.*.url' => 'required|string',
                'medical_documents.*.name' => 'nullable|string',
                'photos' => 'nullable|array',
                'photos.*.url' => 'required_with:photos|string|max:2048',
                'photos.*.name' => 'nullable|string|max:255',
                'photo_url' => 'nullable|string|max:2048',
                'age' => 'nullable|integer|min:0|max:120',
            ],
            LocalizedContent::adminValidationRules('name', true, 255),
            LocalizedContent::adminValidationRules('location', true, 255),
            LocalizedContent::adminValidationRules('condition', true, 255),
            LocalizedContent::adminValidationRules('story', false),
            LocalizedContent::adminValidationRules('short_description', true)
        ));

        $validated = LocalizedContent::syncLegacyColumns(
            LocalizedContent::prepareAdminLocalized(
                $validated,
                ['name', 'location', 'condition', 'story', 'short_description']
            ),
            ['name', 'location', 'condition', 'story', 'short_description']
        );

        $validated['medical_documents'] = $validated['medical_documents'] ?? [];
        $validated['updates'] = $validated['updates'] ?? [];
        $validated = $this->normalizeMediaFields($validated);

        $case = CaseItem::create($validated);


        return response()->json([
            'message' => 'Case yaratildi',
            'data' => new CaseResource($case),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $case = CaseItem::query()
            ->with([
                'donations',
                'helpRequests'
            ])
            ->withCount('donations')
            ->findOrFail($id);

        return response()->json([
            'data' => new CaseResource($case),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $case = CaseItem::query()->findOrFail($id);

        $validated = $request->validate(array_merge(
            [
                'phone' => 'nullable|string|max:50',
                'category' => 'nullable|string|max:100',
                'source' => 'nullable|string|max:100',
                'created_from_request_id' => 'nullable|integer|exists:help_requests,id',
                'status' => 'nullable|string|in:new,draft,active,paused,completed,closed',
                'urgency' => 'nullable|string|max:50',
                'goal_amount' => 'nullable|numeric|min:0',
                'raised_amount' => 'nullable|numeric|min:0',
                'is_urgent' => 'sometimes|boolean',
                'medical_documents' => 'nullable|array',
                'medical_documents.*.url' => 'required_with:medical_documents|string|max:2048',
                'medical_documents.*.name' => 'nullable|string|max:255',
                'photos' => 'nullable|array',
                'photos.*.url' => 'required_with:photos|string|max:2048',
                'photos.*.name' => 'nullable|string|max:255',
                'photo_url' => 'nullable|string|max:2048',
                'age' => 'nullable|integer|min:0|max:120',
            ],
            LocalizedContent::adminValidationRules('name', true, 255),
            LocalizedContent::adminValidationRules('location', true, 255),
            LocalizedContent::adminValidationRules('condition', true, 255),
            LocalizedContent::adminValidationRules('story', false),
            LocalizedContent::adminValidationRules('short_description', true)
        ));

        $validated = LocalizedContent::syncLegacyColumns(
            LocalizedContent::prepareAdminLocalized(
                $validated,
                ['name', 'location', 'condition', 'story', 'short_description']
            ),
            ['name', 'location', 'condition', 'story', 'short_description']
        );

        if (!isset($validated['medical_documents'])) {
            $validated['medical_documents'] = $case->medical_documents ?? [];
        }

        if (!isset($validated['updates'])) {
            $validated['updates'] = $case->updates ?? [];
        }

        $validated = $this->normalizeMediaFields($validated);

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

    private function normalizeMediaFields(array $validated): array
    {
        if (!empty($validated['medical_documents'])) {
            $validated['medical_documents'] = collect($validated['medical_documents'])
                ->map(function ($doc) {
                    if (is_array($doc)) {
                        return [
                            'url' => MediaUrl::publicUrl($doc['url'] ?? null) ?? ($doc['url'] ?? null),
                            'name' => $doc['name'] ?? basename((string) ($doc['url'] ?? '')),
                        ];
                    }

                    return [
                        'url' => MediaUrl::publicUrl($doc) ?? $doc,
                        'name' => basename((string) $doc),
                    ];
                })
                ->values()
                ->all();
        }

        return CasePhotos::syncValidated($validated);
    }
}
