<?php

namespace App\Domains\Knowledge\Actions;

use App\Domains\Knowledge\Exceptions\DuplicateArticleFeedbackException;
use App\Domains\Knowledge\Models\KnowledgeArticle;
use App\Domains\Knowledge\Models\KnowledgeArticleFeedback;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class RecordArticleFeedback
{
    public function execute(
        KnowledgeArticle $article,
        bool $isHelpful,
        ?Authenticatable $user = null,
        ?string $visitorKey = null
    ): KnowledgeArticleFeedback {
        return \DB::transaction(function () use ($article, $isHelpful, $user, $visitorKey) {
            $existingFeedback = null;

            if ($user) {
                $existingFeedback = $article->feedback()
                    ->where('user_id', $user->id)
                    ->first();
            } elseif ($visitorKey) {
                $existingFeedback = $article->feedback()
                    ->where('visitor_key', $visitorKey)
                    ->first();
            }

            if ($existingFeedback) {
                if ($existingFeedback->is_helpful === $isHelpful) {
                    throw new DuplicateArticleFeedbackException;
                }

                $this->adjustCounters($article, $existingFeedback->is_helpful, false);
                $this->adjustCounters($article, $isHelpful, true);

                $existingFeedback->update(['is_helpful' => $isHelpful]);

                return $existingFeedback;
            }

            $feedback = KnowledgeArticleFeedback::create([
                'uuid' => Str::uuid(),
                'knowledge_article_id' => $article->id,
                'user_id' => $user?->id,
                'visitor_key' => $visitorKey,
                'is_helpful' => $isHelpful,
            ]);

            $this->adjustCounters($article, $isHelpful, true);

            return $feedback;
        });
    }

    private function adjustCounters(KnowledgeArticle $article, bool $isHelpful, bool $increment): void
    {
        if ($isHelpful) {
            $article->increment('helpful_count', $increment ? 1 : -1);
        } else {
            $article->increment('not_helpful_count', $increment ? 1 : -1);
        }
    }
}
