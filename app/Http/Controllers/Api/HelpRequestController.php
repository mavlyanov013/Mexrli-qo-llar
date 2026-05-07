<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHelpRequestRequest;
use App\Http\Resources\HelpRequestResource;
use App\Models\CaseItem;
use App\Models\HelpRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HelpRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = HelpRequest::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $items = $query->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'data' => HelpRequestResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function store(StoreHelpRequestRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['description'] = $payload['description'] ?? $payload['situation_description'];
        $payload['category'] = $payload['category'] ?? $payload['support_type'] ?? 'other';
        $payload['attachments'] = $payload['attachments'] ?? $payload['medical_documents'] ?? [];

        if (!isset($payload['status'])) {
            $payload['status'] = 'pending';
        }

        $helpRequest = HelpRequest::create($payload);

        return response()->json([
            'message' => 'Yordam so‘rovi yuborildi',
            'data' => new HelpRequestResource($helpRequest),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $helpRequest = HelpRequest::query()->findOrFail($id);

        return response()->json([
            'data' => new HelpRequestResource($helpRequest),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $helpRequest = HelpRequest::query()->findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|string|in:pending,approved,rejected',
            'admin_notes' => 'nullable|string',
            'case_id' => 'nullable|integer|exists:case_items,id',
        ]);

        $helpRequest->update($validated);

        return response()->json([
            'message' => 'Help request yangilandi',
            'data' => new HelpRequestResource($helpRequest->fresh()),
        ]);
    }

    public function approve(int $id): JsonResponse
    {
        $helpRequest = DB::transaction(function () use ($id) {
            $item = HelpRequest::query()->lockForUpdate()->findOrFail($id);
            $item->update(['status' => 'approved']);

            if (! $item->case_id) {
                $case = CaseItem::create([
                    'name' => $item->full_name,
                    'short_description' => $item->description ?: $item->situation_description,
                    'story' => $item->description ?: $item->situation_description,
                    'phone' => $item->phone,
                    'location' => $item->city,
                    'category' => $item->category ?: $item->support_type ?: 'other',
                    'source' => 'help_request',
                    'created_from_request_id' => $item->id,
                    'status' => 'new',
                    'urgency' => 'medium',
                    'goal_amount' => 0,
                    'raised_amount' => 0,
                    'medical_documents' => $item->attachments ?: $item->medical_documents ?: [],
                ]);

                $item->update([
                    'case_id' => $case->id,
                    'admin_notes' => trim(($item->admin_notes ?? '') . ' | Auto CASE #' . $case->id),
                ]);
            }

            return $item->fresh();
        });

        return response()->json([
            'message' => 'So‘rov tasdiqlandi',
            'data' => new HelpRequestResource($helpRequest),
        ]);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $helpRequest = HelpRequest::query()->findOrFail($id);
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string'],
        ]);

        $helpRequest->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'So‘rov rad etildi',
            'data' => new HelpRequestResource($helpRequest->fresh()),
        ]);
    }

    public function convertToCase(Request $request, int $id): JsonResponse
    {
        $helpRequest = HelpRequest::query()->findOrFail($id);

        if ($helpRequest->case_id) {
            return response()->json([
                'message' => 'Bu so‘rov allaqachon CASE ga aylangan',
                'data' => new HelpRequestResource($helpRequest),
            ]);
        }

        if ($helpRequest->status !== 'approved') {
            return response()->json(['message' => 'Faqat tasdiqlangan so‘rov case bo‘lishi mumkin'], 422);
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'goal_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:new,draft,active,paused,completed,closed'],
        ]);

        $case = CaseItem::create([
            'name' => $validated['name'] ?? $helpRequest->full_name,
            'short_description' => $helpRequest->description ?: $helpRequest->situation_description,
            'story' => $helpRequest->situation_description ?: $helpRequest->description,
            'location' => $helpRequest->city,
            'category' => $helpRequest->category ?: $helpRequest->support_type ?: 'other',
            'goal_amount' => $validated['goal_amount'] ?? 0,
            'raised_amount' => 0,
            'status' => $validated['status'] ?? 'draft',
            'urgency' => 'medium',
            'medical_documents' => $helpRequest->attachments ?: $helpRequest->medical_documents ?: [],
        ]);

        $helpRequest->update([
            'case_id' => $case->id,
            'admin_notes' => trim(($helpRequest->admin_notes ?? '') . ' | CASE yaratildi #' . $case->id),
        ]);

        return response()->json([
            'message' => 'So‘rov CASE ga aylantirildi',
            'data' => new HelpRequestResource($helpRequest->fresh()),
        ]);
    }
}
