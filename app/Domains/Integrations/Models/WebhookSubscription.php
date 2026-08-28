<?php

namespace App\Domains\Integrations\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WebhookSubscription extends Model
{
    protected $fillable = ['name', 'target_url', 'secret', 'event_types', 'created_by_user_id'];
    protected $hidden = ['secret', 'id'];
    protected $casts = ['event_types' => 'array'];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function isActive(): bool
    {
        return $this->is_active && $this->disabled_at === null;
    }

    public function matchesEventType(string $eventType): bool
    {
        return in_array($eventType, $this->event_types ?? [], true);
    }
}
