<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
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
            'type' => $this->type,
            'content' => $this->content ?? [],
            'position' => $this->position,
            'created_at' => $this->created_at?->toISOString(),
            'comments' => BlockCommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
