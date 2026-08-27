<?php

namespace App\Domains\Knowledge\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicKnowledgeArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->title,
            'body' => $this->body,
            'category_id' => $this->knowledge_category_id ? $this->category->uuid : null,
            'helpful_count' => $this->helpful_count,
            'not_helpful_count' => $this->not_helpful_count,
        ];
    }
}
