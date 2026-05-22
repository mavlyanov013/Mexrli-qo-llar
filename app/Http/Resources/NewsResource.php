<?php

namespace App\Http\Resources;

use App\Support\LocalizedContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return LocalizedContent::appendMany($this->resource, ['title', 'excerpt', 'content'], [
            'id' => $this->id,
            'slug' => $this->slug,
            'cover_image' => $this->cover_image,
            'category' => $this->category,
            'author' => $this->author,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
    }
}
