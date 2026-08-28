<?php

namespace App\Domains\Knowledge\Models;

use App\Models\User;
use App\Support\I18n\Casts\BilingualStringCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeArticleVersion extends Model
{
    protected $guarded = ['*'];

    protected $fillable = [
        'knowledge_article_id',
        'version',
        'title',
        'body',
        'visibility',
        'knowledge_category_id',
        'published_by',
        'published_at',
    ];

    protected $casts = [
        'title' => BilingualStringCast::class,
        'body' => BilingualStringCast::class,
        'visibility' => ArticleVisibility::class,
        'published_at' => 'immutable_datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArticle::class, 'knowledge_article_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'knowledge_category_id');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
