<?php

namespace App\Domains\Ai\Models;

enum AiFeature: string
{
    case Summary = 'summary';
    case SuggestedReply = 'suggested_reply';
    case Classification = 'classification';
    case SuggestedArticles = 'suggested_articles';
    case Chatbot = 'chatbot';

    public function configKey(): string
    {
        return match ($this) {
            self::Summary => 'summary',
            self::SuggestedReply => 'suggested_reply',
            self::Classification => 'classification',
            self::SuggestedArticles => 'suggested_articles',
            self::Chatbot => 'chatbot',
        };
    }
}
