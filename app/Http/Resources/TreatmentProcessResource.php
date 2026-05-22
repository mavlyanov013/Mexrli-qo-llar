<?php

namespace App\Http\Resources;

use App\Support\LocalizedContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreatmentProcessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $images = $this->images ?? [];

        return LocalizedContent::appendMany($this->resource, ['title', 'description'], [
            'id' => $this->id,
            'case_id' => $this->case_id,
            'case_name' => $this->whenLoaded('case', fn () => $this->case?->localized('name')),
            'images' => $images,
            'image_count' => count($images),
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
    }
}
