<?php

namespace App\Domains\Integrations\Models;

use App\Support\Models\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WebhookDelivery extends Model
{
    use GeneratesUuid;

    protected $fillable = [
        'webhook_subscription_id', 'event_type', 'event_uuid',
        'payload', 'state', 'attempt_count', 'last_status_code',
        'last_error', 'next_attempt_at', 'delivered_at',
    ];

    protected $hidden = ['id'];

    protected $casts = [
        'payload' => 'array',
        'next_attempt_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(WebhookSubscription::class, 'webhook_subscription_id');
    }

    public function isTerminal(): bool
    {
        return $this->state === 'delivered' || $this->state === 'failed';
    }

    public function isPending(): bool
    {
        return $this->state === 'pending';
    }
}
