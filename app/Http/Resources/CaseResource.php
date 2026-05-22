<?php

namespace App\Http\Resources;

use App\Support\LocalizedContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $payload = LocalizedContent::appendMany($this->resource, [
            'name',
            'location',
            'condition',
            'story',
            'short_description',
        ], [
            'id' => $this->id,
            'age' => $this->age,
            'photo_url' => $this->photo_url,
            'phone' => $this->phone,
            'title' => $this->localized('name'),
            'description' => $this->localized('short_description') ?: $this->localized('story'),
            'goal_amount' => (float) $this->goal_amount,
            'raised_amount' => (float) $this->raised_amount,
            'urgency' => $this->urgency,
            'category' => $this->category,
            'source' => $this->source,
            'created_from_request_id' => $this->created_from_request_id,
            'status' => $this->status,
            'medical_documents' => collect($this->medical_documents ?? [])
                ->map(fn ($doc) => [
                    'url' => $doc['url'] ?? $doc,
                    'name' => $doc['name'] ?? basename($doc),
                ])
                ->values(),
            'updates' => $this->updates ?? [],
            'is_featured' => (bool) $this->is_featured,
            'is_urgent' => (bool) $this->is_urgent,
            'donations_count' => $this->donations_count ?? 0,
            'donations' => $this->whenLoaded('donations'),
            'help_requests' => $this->whenLoaded('helpRequests'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        return $payload;
    }
}
