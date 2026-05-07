<?php

namespace App\Http\Controllers\Api\Admin;

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

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('question', 'like', "%{$search}%");
        }

        $items = $query->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'data' => FaqResource::collection($items->items()),
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
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:150'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $faq = Faq::create($validated);

        return response()->json([
            'message' => 'Savol yaratildi',
            'data' => new FaqResource($faq),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $faq = Faq::query()->findOrFail($id);
        $validated = $request->validate([
            'question' => ['sometimes', 'string', 'max:500'],
            'answer' => ['sometimes', 'string'],
            'category' => ['nullable', 'string', 'max:150'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $faq->update($validated);

        return response()->json([
            'message' => 'Savol yangilandi',
            'data' => new FaqResource($faq->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        Faq::query()->findOrFail($id)->delete();
        return response()->json(['message' => 'Savol o‘chirildi']);
    }
}
