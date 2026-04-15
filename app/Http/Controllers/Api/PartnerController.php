<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;

class PartnerController extends Controller
{
    public function index(): JsonResponse
    {
        $partners = Partner::query()
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $partners,
        ]);
    }
}
