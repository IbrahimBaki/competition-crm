<?php

namespace App\Domains\Knowledge\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KnowledgeArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->title,
            'body' => $this->body,
            'state' => $this->state->value,
            'visibility' => $this->visibility->value,
            'current_version' => $this->current_version,
            'category_id' => $this->knowledge_category_id ? $this->category->uuid : null,
            'author' => [
                'id' => $this->author->uuid,
                'name' => $this->author->name,
            ],
            'published_by' => $this->published_by ? [
                'id' => $this->whenLoaded('publishedBy', fn () => $this->publishedBy->uuid),
                'name' => $this->whenLoaded('publishedBy', fn () => $this->publishedBy->name),
            ] : null,
            'published_at' => $this->published_at,
            'archived_at' => $this->archived_at,
            'helpful_count' => $this->helpful_count,
            'not_helpful_count' => $this->not_helpful_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
