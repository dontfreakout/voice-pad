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
            'size' => $this->formatted_file_size, // Using formatted_file_size accessor
            'length' => $this->formatted_duration, // Using formatted_duration accessor
            'category' => CategoryResource::make($this->whenLoaded('category')),
        ];
    }
}
