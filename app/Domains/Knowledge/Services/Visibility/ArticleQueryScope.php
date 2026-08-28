<?php

namespace App\Domains\Knowledge\Services\Visibility;

use App\Domains\Knowledge\Models\ArticleState;
use App\Domains\Knowledge\Models\Audience;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use Illuminate\Database\Eloquent\Builder;

class ArticleQueryScope
{
    public function forAudience(Audience $audience): Builder
    {
        $query = KnowledgeArticle::query()
            ->whereIn('visibility', $audience->visibilityValues());

        if ($audience !== Audience::Staff) {
            $query->where('state', ArticleState::Published->value);
        }

        return $query;
    }
}
