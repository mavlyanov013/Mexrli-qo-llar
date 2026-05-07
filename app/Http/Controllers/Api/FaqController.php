<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Faq::query()->orderBy('order')->orderByDesc('id');

        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        return response()->json([
            'data' => FaqResource::collection($query->get()),
        ]);
    }
}
