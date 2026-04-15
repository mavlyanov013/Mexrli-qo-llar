<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ContactMessage::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $items = $query->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'data' => ContactMessageResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        $payload = $request->validated();

        if (!isset($payload['status'])) {
            $payload['status'] = 'new';
        }

        $contactMessage = ContactMessage::create($payload);

        return response()->json([
            'message' => 'Xabaringiz muvaffaqiyatli yuborildi',
            'data' => new ContactMessageResource($contactMessage),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $contactMessage = ContactMessage::query()->findOrFail($id);

        return response()->json([
            'data' => new ContactMessageResource($contactMessage),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $contactMessage = ContactMessage::query()->findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|string|in:new,read,replied,archived',
        ]);

        $contactMessage->update($validated);

        return response()->json([
            'message' => 'Contact message yangilandi',
            'data' => new ContactMessageResource($contactMessage->fresh()),
        ]);
    }
}
