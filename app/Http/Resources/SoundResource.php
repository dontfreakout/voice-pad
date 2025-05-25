<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Sound */
class SoundResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'url' => $this->file_url,
            // Formatted file size (e.g., "1.5 MB")
            'size' => $this->formatted_file_size,
            // Formatted duration (e.g., "2:30" for 2 minutes and 30 seconds)
            'length' => $this->formatted_duration,
            // The category relationship, if loaded
            'category' => CategoryResource::make($this->whenLoaded('category')),
        ];
    }
}
