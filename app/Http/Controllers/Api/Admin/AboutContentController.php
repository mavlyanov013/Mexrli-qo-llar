<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamMemberResource;
use App\Models\TeamMember;
use App\Services\AboutContentService;
use App\Support\LocalizedContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function updateBank(Request $request): JsonResponse
    {
        $validated = $request->validate(array_merge(
            [
                'account_uzs' => ['nullable', 'string', 'max:255'],
                'mfo_bik' => ['nullable', 'string', 'max:255'],
            ],
            LocalizedContent::adminValidationRules('bank', true, 255)
        ));

        $validated = LocalizedContent::prepareAdminLocalized($validated, ['bank']);
        $bank = $this->aboutContentService->saveBankDetails($validated);

        return response()->json([
            'message' => 'Bank rekvizitlari saqlandi',
            'data' => $bank,
        ]);
    }

    public function updateLegal(Request $request): JsonResponse
    {
        $validated = $request->validate(array_merge(
            [
                'inn' => ['nullable', 'string', 'max:100'],
            ],
            LocalizedContent::adminValidationRules('org_name', true, 255),
            LocalizedContent::adminValidationRules('legal_address', true, 500)
        ));

        $validated = LocalizedContent::prepareAdminLocalized($validated, ['org_name', 'legal_address']);
        $legal = $this->aboutContentService->saveLegalInfo($validated);

        return response()->json([
            'message' => 'Yuridik ma’lumotlar saqlandi',
            'data' => $legal,
        ]);
    }

    public function updateDocument(Request $request, string $key): JsonResponse
    {
        $validated = $request->validate(array_merge(
            [
                'file' => ['nullable', 'string', 'max:2048'],
                'file_upload' => ['nullable', 'file', 'max:10240'],
            ],
            LocalizedContent::adminValidationRules('title', true, 255),
            LocalizedContent::adminValidationRules('description', true, 500)
        ));

        if ($request->hasFile('file_upload')) {
            $validated['file'] = $request->file('file_upload')->store('uploads/about', 'public');
        }

        unset($validated['file_upload']);

        $validated = LocalizedContent::prepareAdminLocalized($validated, ['title', 'description']);
        $document = $this->aboutContentService->saveDocument($key, $validated);

        return response()->json([
            'message' => 'Hujjat saqlandi',
            'data' => $document,
        ]);
    }

    public function storeTeamMember(Request $request): JsonResponse
    {
        $validated = $request->validate(array_merge(
            [
                'photo' => ['nullable', 'string', 'max:2048'],
                'photo_file' => ['nullable', 'image', 'max:5120'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
            ],
            LocalizedContent::adminValidationRules('name', true, 255),
            LocalizedContent::adminValidationRules('position', true, 255)
        ));

        if ($request->hasFile('photo_file')) {
            $validated['photo'] = $request->file('photo_file')->store('uploads/team', 'public');
        }

        unset($validated['photo_file']);

        $validated = LocalizedContent::syncLegacyColumns(
            LocalizedContent::prepareAdminLocalized($validated, ['name', 'position']),
            ['name', 'position']
        );

        $member = TeamMember::create($validated);

        return response()->json([
            'message' => 'Jamoa a’zosi qo‘shildi',
            'data' => new TeamMemberResource($member),
        ], 201);
    }

    public function updateTeamMember(Request $request, int $id): JsonResponse
    {
        $member = TeamMember::query()->findOrFail($id);

        $validated = $request->validate(array_merge(
            [
                'photo' => ['nullable', 'string', 'max:2048'],
                'photo_file' => ['nullable', 'image', 'max:5120'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
            ],
            LocalizedContent::adminValidationRules('name', true, 255),
            LocalizedContent::adminValidationRules('position', true, 255)
        ));

        if ($request->hasFile('photo_file')) {
            if ($member->photo && ! str_starts_with($member->photo, 'http')) {
                Storage::disk('public')->delete($member->photo);
            }

            $validated['photo'] = $request->file('photo_file')->store('uploads/team', 'public');
        }

        unset($validated['photo_file']);

        $validated = LocalizedContent::syncLegacyColumns(
            LocalizedContent::prepareAdminLocalized($validated, ['name', 'position']),
            ['name', 'position']
        );

        $member->update($validated);

        return response()->json([
            'message' => 'Jamoa a’zosi yangilandi',
            'data' => new TeamMemberResource($member->fresh()),
        ]);
    }

    public function destroyTeamMember(int $id): JsonResponse
    {
        $member = TeamMember::query()->findOrFail($id);

        if ($member->photo && ! str_starts_with($member->photo, 'http')) {
            Storage::disk('public')->delete($member->photo);
        }

        $member->delete();

        return response()->json([
            'message' => 'Jamoa a’zosi o‘chirildi',
        ]);
    }
}
