<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'parent_id' => $this->parent_id,
            'title' => $this->title,
            'icon' => $this->icon ?? '📄',
            'cover_image' => $this->cover_image,
            'cover_color' => $this->cover_color,
            'is_favorite' => (bool) $this->is_favorite,
            'created_by' => $this->created_by,
            'user_role' => $this->getUserRole($user),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'parent' => new self($this->whenLoaded('parent')),
            'children' => self::collection($this->whenLoaded('children')),
            'blocks' => BlockResource::collection($this->whenLoaded('blocks')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'databases' => DatabaseResource::collection($this->whenLoaded('databases')),
            'permissions' => PagePermissionResource::collection($this->whenLoaded('permissions')),
            'activities' => PageActivityResource::collection($this->whenLoaded('activities')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
