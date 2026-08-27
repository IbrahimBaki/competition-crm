<?php

namespace App\Domains\Knowledge\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KnowledgeArticleVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'version' => $this->version,
            'title' => $this->title,
            'body' => $this->body,
            'visibility' => $this->visibility->value,
            'category_id' => $this->knowledge_category_id ? $this->category->uuid : null,
            'published_by' => $this->published_by ? [
                'id' => $this->publishedBy->uuid,
                'name' => $this->publishedBy->name,
            ] : null,
            'published_at' => $this->published_at,
        ];
    }
}
