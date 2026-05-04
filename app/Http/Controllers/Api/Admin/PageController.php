<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Page::query()->with('sections')->latest();
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        }
        $pages = $query->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'message' => 'Pages fetched successfully',
            'data' => PageResource::collection($pages->items()),
            'meta' => [
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:pages,slug'],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $page = Page::query()->create($validated);

        return response()->json([
            'message' => 'Page created successfully',
            'data' => new PageResource($page->load('sections')),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $page = Page::query()->with('sections')->findOrFail($id);
        return response()->json(['message' => 'Page fetched successfully', 'data' => new PageResource($page)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $page = Page::query()->findOrFail($id);
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:pages,slug,' . $id],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);
        $page->update($validated);
        return response()->json(['message' => 'Page updated successfully', 'data' => new PageResource($page->fresh()->load('sections'))]);
    }

    public function destroy(int $id): JsonResponse
    {
        $page = Page::query()->findOrFail($id);
        $page->delete();
        return response()->json(['message' => 'Page deleted successfully']);
    }
}
