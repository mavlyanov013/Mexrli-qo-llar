<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    public function home(): JsonResponse
    {
        $page = Page::query()
            ->with(['sections' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
            ->where('slug', 'home')
            ->where('is_active', true)
            ->first();

        if (! $page) {
            return response()->json([
                'message' => 'Home page not found',
            ], 404);
        }

        return response()->json([
            'data' => new PageResource($page),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $page = Page::query()
            ->with(['sections' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $page) {
            return response()->json([
                'message' => 'Page not found',
            ], 404);
        }

        return response()->json([
            'data' => new PageResource($page),
        ]);
    }
}
