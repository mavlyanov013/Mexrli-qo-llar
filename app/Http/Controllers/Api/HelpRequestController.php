<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHelpRequestRequest;
use App\Http\Requests\UpdateHelpRequestStatusRequest;
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
            'status' => 'sometimes|string|in:pending,tasdiqlandi,rad_etildi,rezerv,approved,rejected',
            'admin_notes' => 'nullable|string',
            'case_id' => 'nullable|integer|exists:case_items,id',
        ]);

        $helpRequest->update($validated);

        return response()->json([
            'message' => 'Help request yangilandi',
            'data' => new HelpRequestResource($helpRequest->fresh()),
        ]);
    }

    public function updateStatus(UpdateHelpRequestStatusRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        return $this->applyStatus(
            $id,
            $validated['status'],
            $validated['admin_notes'] ?? null
        );
    }

    public function approve(int $id): JsonResponse
    {
        return $this->applyStatus($id, 'tasdiqlandi');
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string'],
        ]);

        return $this->applyStatus($id, 'rad_etildi', $validated['admin_notes'] ?? null);
    }

    private function applyStatus(int $id, string $status, ?string $adminNotes = null): JsonResponse
    {
        $status = $this->normalizeStatus($status);

        $result = DB::transaction(function () use ($id, $status, $adminNotes) {
            $item = HelpRequest::query()->lockForUpdate()->findOrFail($id);

            $item->update([
                'status' => $status,
                'admin_notes' => $adminNotes ?? $item->admin_notes,
            ]);

            $caseId = null;

            if ($status === 'tasdiqlandi') {
                $caseId = $this->createCaseFromHelpRequest($item);
            }

            $item = $item->fresh();

            return [
                'help_request' => $item,
                'case_id' => $caseId ?? $item->case_id,
            ];
        });

        $message = match ($status) {
            'tasdiqlandi' => 'So‘rov tasdiqlandi va holat yaratildi',
            'rad_etildi' => 'So‘rov rad etildi',
            'rezerv' => 'So‘rov rezervga olindi',
            default => 'Holat yangilandi',
        };

        return response()->json([
            'message' => $message,
            'data' => new HelpRequestResource($result['help_request']),
            'case_id' => $result['case_id'],
            'redirect_to' => $status === 'tasdiqlandi' ? 'cases' : null,
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

        if (! in_array($helpRequest->status, ['tasdiqlandi', 'approved'], true)) {
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

    private function normalizeStatus(string $status): string
    {
        return match ($status) {
            'approved' => 'tasdiqlandi',
            'rejected' => 'rad_etildi',
            default => $status,
        };
    }

    private function createCaseFromHelpRequest(HelpRequest $item): int
    {
        if ($item->case_id) {
            return (int) $item->case_id;
        }

        $documents = array_values(array_filter(array_merge(
            $item->attachments ?? [],
            $item->medical_documents ?? [],
            $item->photos ?? [],
        )));

        $case = CaseItem::create([
            'name' => $item->full_name,
            'short_description' => $item->description ?: $item->situation_description,
            'story' => $item->situation_description ?: $item->description,
            'phone' => $item->phone,
            'location' => $item->city,
            'category' => $item->category ?: $item->support_type ?: 'other',
            'source' => 'help_request',
            'created_from_request_id' => $item->id,
            'status' => 'new',
            'urgency' => 'medium',
            'goal_amount' => 0,
            'raised_amount' => 0,
            'medical_documents' => $documents,
        ]);

        $item->update([
            'case_id' => $case->id,
            'admin_notes' => trim(($item->admin_notes ?? '') . ' | Auto CASE #' . $case->id),
        ]);

        return $case->id;
    }
}
