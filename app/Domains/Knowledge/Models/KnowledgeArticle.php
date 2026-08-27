<?php

namespace App\Domains\Knowledge\Models;

use App\Models\User;
use App\Support\I18n\Casts\BilingualStringCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeArticle extends Model
{
    protected $guarded = ['*'];

    protected $fillable = [
        'knowledge_category_id',
        'slug',
        'title',
        'body',
        'state',
        'visibility',
        'current_version',
        'search_ar',
        'search_en',
        'author_id',
        'published_by',
        'published_at',
        'archived_at',
        'helpful_count',
        'not_helpful_count',
    ];

    protected $casts = [
        'title' => BilingualStringCast::class,
        'body' => BilingualStringCast::class,
        'state' => ArticleState::class,
        'visibility' => ArticleVisibility::class,
        'published_at' => 'immutable_datetime',
        'archived_at' => 'immutable_datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'knowledge_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(KnowledgeArticleVersion::class, 'knowledge_article_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(KnowledgeArticleFeedback::class, 'knowledge_article_id');
    }
}
