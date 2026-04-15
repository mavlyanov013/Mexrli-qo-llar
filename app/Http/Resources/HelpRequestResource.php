<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HelpRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'city' => $this->city,
            'situation_description' => $this->situation_description,
            'support_type' => $this->support_type,
            'medical_documents' => $this->medical_documents,
            'photos' => $this->photos,
            'consent_given' => $this->consent_given,
            'status' => $this->status,
            'admin_notes' => $this->admin_notes,
            'case_id' => $this->case_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
