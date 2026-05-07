<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsResource;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::query()->latest('published_at')->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $items = $query->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'data' => NewsResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:blog_posts,slug'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['title']) . '-' . Str::random(5);
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $item = BlogPost::create($validated);

        return response()->json([
            'message' => 'Yangilik yaratildi',
            'data' => new NewsResource($item),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => new NewsResource(BlogPost::query()->findOrFail($id)),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $item = BlogPost::query()->findOrFail($id);
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:blog_posts,slug,' . $id],
            'content' => ['sometimes', 'string'],
            'excerpt' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
        ]);

        if (array_key_exists('title', $validated) && empty($validated['slug']) && empty($item->slug)) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        }
        if (($validated['status'] ?? $item->status) === 'published' && empty($validated['published_at']) && empty($item->published_at)) {
            $validated['published_at'] = now();
        }

        $item->update($validated);

        return response()->json([
            'message' => 'Yangilik yangilandi',
            'data' => new NewsResource($item->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        BlogPost::query()->findOrFail($id)->delete();
        return response()->json(['message' => 'Yangilik o‘chirildi']);
    }
}
