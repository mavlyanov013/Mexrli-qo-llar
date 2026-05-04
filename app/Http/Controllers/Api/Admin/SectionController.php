<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SectionResource;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page_id' => ['required', 'exists:pages,id'],
            'type' => ['required', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'extra' => ['nullable', 'array'],
        ]);

        $section = Section::query()->create($validated);

        return response()->json([
            'message' => 'Section created successfully',
            'data' => new SectionResource($section),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $section = Section::query()->findOrFail($id);
        $validated = $request->validate([
            'type' => ['sometimes', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'extra' => ['nullable', 'array'],
        ]);
        $section->update($validated);

        return response()->json([
            'message' => 'Section updated successfully',
            'data' => new SectionResource($section->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $section = Section::query()->findOrFail($id);
        $section->delete();
        return response()->json(['message' => 'Section deleted successfully']);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:sections,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['items'] as $item) {
            Section::query()->whereKey($item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Sections reordered successfully']);
    }
}
