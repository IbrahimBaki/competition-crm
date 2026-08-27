<?php

namespace App\Domains\Knowledge\Services\Search;

use App\Domains\Customers\Services\TextNormaliser;
use App\Domains\Knowledge\Models\Audience;
use App\Domains\Knowledge\Services\Visibility\ArticleQueryScope;
use Illuminate\Database\Eloquent\Builder;

readonly class SearchArticles
{
    public function __construct(
        private ArticleQueryScope $queryScope,
        private TextNormaliser $normaliser,
    ) {}

    public function search(string $query, Audience $audience): Builder
    {
        $builder = $this->queryScope->forAudience($audience);

        if (empty(trim($query))) {
            return $builder;
        }

        $normalised = $this->normaliser->normaliseName($query);
        $normalisedEn = strtolower(preg_replace('/\s+/', ' ', trim($query)));

        return $builder->where(function (Builder $q) use ($normalised, $normalisedEn) {
            $q->where('search_ar', 'like', "%{$normalised}%")
                ->orWhere('search_en', 'like', "%{$normalisedEn}%");
        });
    }
}
