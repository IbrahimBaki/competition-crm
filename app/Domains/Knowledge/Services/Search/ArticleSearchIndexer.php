<?php

namespace App\Domains\Knowledge\Services\Search;

use App\Domains\Customers\Services\TextNormaliser;
use App\Domains\Knowledge\Models\KnowledgeArticle;

readonly class ArticleSearchIndexer
{
    public function __construct(
        private TextNormaliser $normaliser,
    ) {}

    public function build(KnowledgeArticle $article): array
    {
        $arTitle = $article->title['ar'] ?? '';
        $arBody = $article->body['ar'] ?? '';
        $enTitle = $article->title['en'] ?? '';
        $enBody = $article->body['en'] ?? '';

        $arText = $arTitle.' '.$arBody;
        $enText = $enTitle.' '.$enBody;

        return [
            'search_ar' => $this->normaliser->normaliseName($arText),
            'search_en' => strtolower(preg_replace('/\s+/', ' ', trim($enText))),
        ];
    }
}
