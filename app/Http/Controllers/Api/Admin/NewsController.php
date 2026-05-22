<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsResource;
use App\Models\BlogPost;
use App\Support\LocalizedContent;
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

        $items = $query->paginate((int) $request->input('per_page', 12));

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
        $validated = $request->validate(array_merge(
            [
                'slug' => ['nullable', 'string', 'max:255', 'unique:blog_posts,slug'],
                'cover_image' => ['nullable', 'string', 'max:2048'],
                'status' => ['required', 'in:draft,published'],
                'published_at' => ['nullable', 'date'],
                'category' => ['required', 'in:news,success_story,announcement,helped_child'],
            ],
            LocalizedContent::adminValidationRules('title', true, 255),
            LocalizedContent::adminValidationRules('content', true),
            LocalizedContent::adminValidationRules('excerpt', true)
        ));

        $validated = LocalizedContent::syncLegacyColumns(
            LocalizedContent::prepareAdminLocalized($validated, ['title', 'excerpt', 'content']),
            ['title', 'excerpt', 'content']
        );
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['title_uz']) . '-' . Str::random(5);
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
        $validated = $request->validate(array_merge(
            [
                'slug' => ['nullable', 'string', 'max:255', 'unique:blog_posts,slug,' . $id],
                'cover_image' => ['nullable', 'string', 'max:2048'],
                'status' => ['sometimes', 'in:draft,published'],
                'published_at' => ['nullable', 'date'],
                'category' => ['sometimes', 'required', 'in:news,success_story,announcement,helped_child'],
            ],
            LocalizedContent::adminValidationRules('title', true, 255),
            LocalizedContent::adminValidationRules('content', true),
            LocalizedContent::adminValidationRules('excerpt', true)
        ));

        $validated = LocalizedContent::syncLegacyColumns(
            LocalizedContent::prepareAdminLocalized($validated, ['title', 'excerpt', 'content']),
            ['title', 'excerpt', 'content']
        );

        if (! empty($validated['title_uz']) && empty($validated['slug']) && empty($item->slug)) {
            $validated['slug'] = Str::slug($validated['title_uz']) . '-' . Str::random(5);
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
