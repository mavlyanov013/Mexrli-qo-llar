<?php

namespace App\Http\Resources;

use App\Enums\SectionType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => SectionType::tryFrom($this->type)?->value ?? $this->type,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'content' => $this->content,
            'image' => $this->image,
            'sort_order' => $this->sort_order,
            'extra' => is_array($this->extra)
                ? $this->extra
                : (json_decode($this->extra ?? '{}', true) ?? []),
        ];
    }
}
