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
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'directory' => ['nullable', 'string'],
        ]);

        if (!$request->hasFile('file')) {
            return response()->json([
                'message' => 'No file received',
            ], 422);
        }

        $file = $request->file('file');

        if (!$file->isValid()) {
            return response()->json([
                'message' => 'Invalid uploaded file',
                'error' => $file->getErrorMessage(),
                'code' => $file->getError(),
            ], 422);
        }

        $directory = trim($request->input('directory', 'admin'), '/');

        $path = $file->store("uploads/{$directory}", 'public');

        return response()->json([
            'message' => 'Uploaded successfully',
            'data' => [
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
            ],
        ]);
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
