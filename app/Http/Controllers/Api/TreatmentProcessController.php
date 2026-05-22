<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TreatmentProcessResource;
use App\Models\TreatmentProcess;
use Illuminate\Http\JsonResponse;

class TreatmentProcessController extends Controller
{
    public function indexByCase(int $caseId): JsonResponse
    {
        $items = TreatmentProcess::query()
            ->where('case_id', $caseId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => TreatmentProcessResource::collection($items),
        ]);
    }
}
