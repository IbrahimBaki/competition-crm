<?php

namespace App\Domains\Knowledge\Models;

use App\Support\I18n\Casts\BilingualStringCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeCategory extends Model
{
    use HasFactory;

    protected $guarded = ['*'];

    protected $fillable = ['parent_id', 'code', 'name', 'depth', 'position', 'is_active'];

    protected $casts = [
        'name' => BilingualStringCast::class,
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(KnowledgeCategory::class, 'parent_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(KnowledgeArticle::class, 'knowledge_category_id');
    }

    /**
     * Get all ancestors up to the root (max 3 hops for defensive loop).
     *
     * @return array<int, KnowledgeCategory>
     */
    public function ancestors(): array
    {
        $ancestors = [];
        $current = $this;
        $hops = 0;

        while ($current && $current->parent_id && $hops < 3) {
            $current = $current->parent;
            if ($current) {
                $ancestors[] = $current;
            }
            $hops++;
        }

        return $ancestors;
    }
}
