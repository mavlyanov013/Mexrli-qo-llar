<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_id' => $this->case_id,
            'donor_name' => $this->donor_name,
            'donor_phone' => $this->donor_phone,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'type' => $this->type,
            'message' => $this->message,
            'is_anonymous' => $this->is_anonymous,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
