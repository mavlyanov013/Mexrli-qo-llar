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
            'condition' => $this->condition,
            'story' => $this->story,
            'short_description' => $this->short_description,
            'goal_amount' => $this->goal_amount,
            'raised_amount' => $this->raised_amount,
            'urgency' => $this->urgency,
            'category' => $this->category,
            'status' => $this->status,
            'medical_documents' => $this->medical_documents,
            'updates' => $this->updates,
            'is_featured' => $this->is_featured,
            'is_urgent' => $this->is_urgent,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
