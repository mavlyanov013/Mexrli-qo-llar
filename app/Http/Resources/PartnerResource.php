<?php

namespace App\Http\Resources;

use App\Support\LocalizedContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return LocalizedContent::appendMany($this->resource, ['name', 'description'], [
            'id' => $this->id,
            'logo_url' => $this->logo_url,
            'website' => $this->website,
            'type' => $this->type,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
    }
}
