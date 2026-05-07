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
    /* ================= LIST ================= */
    public function index(Request $request): JsonResponse
    {
        $query = ContactMessage::query()->latest();

        // filter by status (admin panel uchun)
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $items = $query->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'data' => ContactMessageResource::collection($items),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    /* ================= STORE (USER SEND MESSAGE) ================= */
    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        $payload = $request->validated();

        // ⚠️ HAR DOIM NEW BO'LADI (user hech qachon status yubormaydi)
        $payload['status'] = 'new';

        $message = ContactMessage::create($payload);

        return response()->json([
            'message' => 'Xabaringiz muvaffaqiyatli yuborildi',
            'data'    => new ContactMessageResource($message),
        ], 201);
    }

    /* ================= SHOW ================= */
    public function show(int $id): JsonResponse
    {
        $message = ContactMessage::findOrFail($id);

        return response()->json([
            'data' => new ContactMessageResource($message),
        ]);
    }

    /* ================= UPDATE (ADMIN ONLY) ================= */
    public function update(Request $request, int $id): JsonResponse
    {
        $message = ContactMessage::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:new,read,replied,archived',
        ]);

        $message->update($validated);

        return response()->json([
            'message' => 'Xabar holati yangilandi',
            'data'    => new ContactMessageResource($message->fresh()),
        ]);
    }

    /* ================= DELETE ================= */
    public function destroy(int $id): JsonResponse
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return response()->json([
            'message' => 'Xabar o‘chirildi',
        ]);
    }
}
