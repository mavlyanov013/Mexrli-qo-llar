<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    public function index(): JsonResponse
    {
        $reports = FinancialReport::query()
            ->latest()
            ->get();

        return response()->json([
            'data' => $reports,
        ]);
    }

    public function latest(): JsonResponse
    {
        $report = FinancialReport::query()
            ->latest()
            ->first();

        return response()->json([
            'data' => $report,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $report = FinancialReport::query()->findOrFail($id);

        return response()->json([
            'data' => $report,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'period' => ['nullable', 'string', 'max:100'],
            'total_received' => ['nullable', 'numeric', 'min:0'],
            'total_spent' => ['nullable', 'numeric', 'min:0'],
            'report_file' => ['nullable', 'string', 'max:500'],
            'published_at' => ['nullable', 'date'],
        ]);
        $report = FinancialReport::query()->create($validated);
        return response()->json(['message' => 'Report created successfully', 'data' => $report], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $report = FinancialReport::query()->findOrFail($id);
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'period' => ['nullable', 'string', 'max:100'],
            'total_received' => ['nullable', 'numeric', 'min:0'],
            'total_spent' => ['nullable', 'numeric', 'min:0'],
            'report_file' => ['nullable', 'string', 'max:500'],
            'published_at' => ['nullable', 'date'],
        ]);
        $report->update($validated);
        return response()->json(['message' => 'Report updated successfully', 'data' => $report->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        $report = FinancialReport::query()->findOrFail($id);
        $report->delete();
        return response()->json(['message' => 'Report deleted successfully']);
    }
}
