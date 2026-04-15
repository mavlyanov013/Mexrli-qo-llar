<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialReport;
use Illuminate\Http\JsonResponse;

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
}
