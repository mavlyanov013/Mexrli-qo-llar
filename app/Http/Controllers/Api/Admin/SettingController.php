<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Settings fetched successfully',
            'data' => Setting::query()->orderBy('key')->get(),
        ]);
    }

    public function upsert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.key' => ['required', 'string', 'max:255'],
            'items.*.value' => ['nullable'],
        ]);

        foreach ($validated['items'] as $item) {
            Setting::query()->updateOrCreate(
                ['key' => $item['key']],
                ['value' => is_scalar($item['value']) || $item['value'] === null ? $item['value'] : json_encode($item['value'])]
            );
        }

        return response()->json([
            'message' => 'Settings updated successfully',
            'data' => Setting::query()->orderBy('key')->get(),
        ]);
    }
}
