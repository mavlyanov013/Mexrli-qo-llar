<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ContactInfoService;
use Illuminate\Http\JsonResponse;

class ContactInfoController extends Controller
{
    public function __construct(
        private readonly ContactInfoService $contactInfoService
    ) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->contactInfoService->get(),
        ]);
    }
}
