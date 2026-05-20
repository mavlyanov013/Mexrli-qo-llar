<?php

namespace App\Http\Resources;

use App\Enums\SectionType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'file_name' => $this->file_name,
            'file_url' => $this->file_path
                ? Storage::disk('public')->url($this->file_path)
                : null,
            'image_url' => $this->image
                ? Storage::disk('public')->url($this->image)
                : null,
            'sort_order' => $this->sort_order,
            'extra' => is_array($this->extra)
                ? $this->extra
                : (json_decode($this->extra ?? '{}', true) ?? []),
        ];
    }
}
