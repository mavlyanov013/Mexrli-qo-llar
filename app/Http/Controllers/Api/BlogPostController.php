<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::query()
            ->where('status', 'published')
            ->latest('published_at')
            ->latest('id');

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->has('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        $perPage = (int) $request->input('per_page', 9);
        $posts = $query->paginate($perPage);

        return response()->json([
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_featured' => 'sometimes|boolean',
            'status' => 'nullable|string|max:50',
        ]);

        $post = BlogPost::create($validated);

        return response()->json([
            'message' => 'Blog post yaratildi',
            'data' => $post,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $post = BlogPost::query()->findOrFail($id);

        return response()->json([
            'data' => $post,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $post = BlogPost::query()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,' . $id,
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_featured' => 'sometimes|boolean',
            'status' => 'nullable|string|max:50',
        ]);

        $post->update($validated);

        return response()->json([
            'message' => 'Blog post yangilandi',
            'data' => $post->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $post = BlogPost::query()->findOrFail($id);
        $post->delete();

        return response()->json([
            'message' => 'Blog post o‘chirildi',
        ]);
    }
}
