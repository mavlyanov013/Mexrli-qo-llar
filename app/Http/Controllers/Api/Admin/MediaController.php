<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function store(Request $request): JsonResponse
    {

        $directory = trim((string) ($validated['directory'] ?? 'admin'), '/');
        $path = $request->file('file')->store("uploads/{$directory}", 'public');

        return response()->json([
            'message' => 'Media uploaded successfully',
            'data' => [
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
            ],
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'path' => ['required', 'string'],
        ]);

        Storage::disk('public')->delete($validated['path']);

        return response()->json([
            'message' => 'Media deleted successfully',
        ]);
    }
}
