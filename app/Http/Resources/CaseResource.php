<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'age' => $this->age,
            'photo_url' => $this->photo_url,
            'location' => $this->location,
            'phone' => $this->phone,
            'condition' => $this->condition,
            'story' => $this->story,
            'short_description' => $this->short_description,
            'title' => $this->name,
            'description' => $this->short_description ?: $this->story,

            'goal_amount' => (float) $this->goal_amount,
            'raised_amount' => (float) $this->raised_amount,

            'urgency' => $this->urgency,
            'category' => $this->category,
            'source' => $this->source,
            'created_from_request_id' => $this->created_from_request_id,
            'status' => $this->status,

            // arrays
            'medical_documents' => collect($this->medical_documents ?? [])
                ->map(fn($doc) => [
                    'url' => $doc['url'] ?? $doc,
                    'name' => $doc['name'] ?? basename($doc),
                ])
                ->values(),
            'updates' => $this->updates ?? [],

            // flags
            'is_featured' => (bool) $this->is_featured,
            'is_urgent' => (bool) $this->is_urgent,

            // relations (MUHIM)
            'donations_count' => $this->donations_count ?? 0,
            'donations' => $this->whenLoaded('donations'),
            'help_requests' => $this->whenLoaded('helpRequests'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
