<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VolunteerApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'city' => $this->city,
            'role_interest' => $this->role_interest,
            'message' => $this->message,
            'admin_notes' => $this->admin_notes,
            'experience' => $this->experience,
            'motivation' => $this->motivation,
            'availability' => $this->availability,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
