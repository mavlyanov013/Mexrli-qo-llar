<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamMemberResource;
use App\Services\AboutContentService;
use Illuminate\Http\JsonResponse;

class AboutContentController extends Controller
{
    public function __construct(private readonly AboutContentService $aboutContentService)
    {
    }

    public function show(): JsonResponse
    {
        $content = $this->aboutContentService->getContent();

        return response()->json([
            'data' => [
                'bank' => $content['bank'],
                'legal' => $content['legal'],
                'docs' => $content['docs'],
                'team' => TeamMemberResource::collection($content['team']),
            ],
        ]);
    }
}
