<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateContactInfoRequest;
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

    public function update(UpdateContactInfoRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'Aloqa ma’lumotlari yangilandi',
            'data' => $this->contactInfoService->update($request->validated()),
        ]);
    }
}
