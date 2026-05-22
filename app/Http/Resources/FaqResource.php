<?php

namespace App\Http\Resources;

use App\Support\LocalizedContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return LocalizedContent::appendMany($this->resource, ['question', 'answer'], [
            'id' => $this->id,
            'category' => $this->category,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
    }
}
