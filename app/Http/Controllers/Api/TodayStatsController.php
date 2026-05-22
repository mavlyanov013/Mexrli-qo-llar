<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TodayStatsService;
use Illuminate\Http\JsonResponse;

class TodayStatsController extends Controller
{
    public function __construct(
        private readonly TodayStatsService $todayStatsService
    ) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->todayStatsService->get(),
        ]);
    }
}
