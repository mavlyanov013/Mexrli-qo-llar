<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsResource;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::query()->latest('published_at')->latest('id');

        if ($request->boolean('published_only', true)) {
            $query->where('status', 'published');
        }

        $items = $query->paginate((int) $request->input('per_page', 9));

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

    public function show(string $slug): JsonResponse
    {
        $item = BlogPost::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return response()->json([
            'data' => new NewsResource($item),
        ]);
    }
}
