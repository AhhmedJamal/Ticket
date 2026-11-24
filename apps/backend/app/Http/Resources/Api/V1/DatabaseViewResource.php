<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DatabaseViewResource extends JsonResource
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
            'notion_database_id' => $this->notion_database_id,
            'name' => $this->name,
            'type' => $this->type,
            'filter_config' => $this->filter_config,
            'sort_config' => $this->sort_config,
            'visible_properties' => $this->visible_properties,
            'position' => $this->position,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
