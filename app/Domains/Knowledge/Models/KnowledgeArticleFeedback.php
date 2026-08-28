<?php

namespace App\Domains\Knowledge\Models;

use App\Models\User;
use App\Support\Models\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeArticleFeedback extends Model
{
    use GeneratesUuid;

    protected $guarded = ['*'];

    protected $fillable = [
        'knowledge_article_id',
        'user_id',
        'visitor_key',
        'is_helpful',
    ];

    protected $casts = [
        'is_helpful' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArticle::class, 'knowledge_article_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
