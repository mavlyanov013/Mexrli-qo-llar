<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SectionResource;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use App\Enums\SectionType;
use Illuminate\Support\Facades\Storage;
class SectionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page_id' => ['required', 'exists:pages,id'],
            'type' => ['required'],

            'title' => ['nullable', 'string'],
            'subtitle' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],

            'file' => ['nullable', 'file', 'max:10240'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],

            'sort_order' => ['nullable', 'integer'],
            'extra' => ['nullable', 'array'],
        ]);

        // 📁 FILE
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $path = $file->store('uploads/sections/files', 'public');

            // ❗ PATH SAQLANADI, URL EMAS
            $validated['file_path'] = $path;
            $validated['file_name'] = $file->getClientOriginalName();
        }

        // 🖼 IMAGE
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            $path = $image->store('uploads/sections/images', 'public');

            $validated['image'] = $path;
        }

        $section = Section::create($validated);

        return response()->json([
            'message' => 'Section created',
            'data' => new SectionResource($section),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $section = Section::findOrFail($id);

        $validated = $request->validate([
            'type' => ['sometimes'],
            'title' => ['nullable', 'string'],
            'subtitle' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],

            'file' => ['nullable', 'file', 'max:10240'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],

            'sort_order' => ['nullable', 'integer'],
            'extra' => ['nullable', 'array'],
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $path = $file->store('uploads/sections/files', 'public');

            $validated['file_path'] = $path;
            $validated['file_name'] = $file->getClientOriginalName();
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            $path = $image->store('uploads/sections/images', 'public');

            $validated['image'] = $path;
        }

        $section->update($validated);

        return response()->json([
            'message' => 'Updated',
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
