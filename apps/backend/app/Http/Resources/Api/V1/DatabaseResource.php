<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DatabaseResource extends JsonResource
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
            'page_id' => $this->page_id,
            'name' => $this->name,
            'properties' => $this->properties ?? [],
            'rows' => DatabaseRowResource::collection($this->whenLoaded('rows')),
            'views' => DatabaseViewResource::collection($this->whenLoaded('views')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
