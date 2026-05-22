<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTreatmentProcessRequest;
use App\Http\Requests\Admin\UpdateTreatmentProcessRequest;
use App\Http\Resources\TreatmentProcessResource;
use App\Models\TreatmentProcess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TreatmentProcessController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TreatmentProcess::query()
            ->with('case')
            ->orderBy('sort_order')
            ->orderByDesc('id');

        if ($request->filled('case_id')) {
            $query->where('case_id', (int) $request->input('case_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'data' => TreatmentProcessResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function store(StoreTreatmentProcessRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['images'] = $this->resolveImages($request, $validated['images'] ?? []);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $item = TreatmentProcess::create($validated);

        return response()->json([
            'message' => 'Davolanish jarayoni yaratildi',
            'data' => new TreatmentProcessResource($item),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => new TreatmentProcessResource(TreatmentProcess::query()->findOrFail($id)),
        ]);
    }

    public function update(UpdateTreatmentProcessRequest $request, int $id): JsonResponse
    {
        $item = TreatmentProcess::query()->findOrFail($id);
        $validated = $request->validated();

        if ($request->hasFile('images') || array_key_exists('images', $validated)) {
            $validated['images'] = $this->resolveImages(
                $request,
                $validated['images'] ?? $item->images ?? []
            );
        }

        $item->update($validated);

        return response()->json([
            'message' => 'Davolanish jarayoni yangilandi',
            'data' => new TreatmentProcessResource($item->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $item = TreatmentProcess::query()->findOrFail($id);
        $this->deleteStoredImages($item->images ?? []);
        $item->delete();

        return response()->json(['message' => 'Davolanish jarayoni o‘chirildi']);
    }

    private function resolveImages(Request $request, array $existingPaths = []): array
    {
        $images = collect($existingPaths)
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->values()
            ->all();

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file instanceof UploadedFile && $file->isValid()) {
                    $images[] = $this->storeUploadedImage($file);
                }
            }
        }

        return array_values(array_unique($images));
    }

    private function storeUploadedImage(UploadedFile $file): string
    {
        $path = $file->store('treatment_processes', 'public');

        return Storage::disk('public')->url($path);
    }

    private function deleteStoredImages(array $images): void
    {
        foreach ($images as $image) {
            if (! is_string($image) || $image === '') {
                continue;
            }

            $path = parse_url($image, PHP_URL_PATH);
            if (! $path) {
                continue;
            }

            $storagePath = ltrim(str_replace('/storage/', '', $path), '/');
            if ($storagePath !== '') {
                Storage::disk('public')->delete($storagePath);
            }
        }
    }
}
