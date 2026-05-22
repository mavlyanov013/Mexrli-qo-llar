<?php

namespace App\Http\Resources;

use App\Support\LocalizedContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TeamMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return LocalizedContent::appendMany($this->resource, ['name', 'position'], [
            'id' => $this->id,
            'photo' => $this->photo,
            'photo_url' => $this->resolvePhotoUrl(),
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
    }

    private function resolvePhotoUrl(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        if (str_starts_with($this->photo, 'http') || str_starts_with($this->photo, '/')) {
            return $this->photo;
        }

        return Storage::disk('public')->url($this->photo);
    }
}
