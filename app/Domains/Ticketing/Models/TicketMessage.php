<?php

namespace App\Domains\Ticketing\Models;

use App\Domains\Customers\Models\CustomerContact;
use App\Models\User;
use App\Support\Attachments\Attachment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TicketMessage extends Model
{
    protected $guarded = ['*'];

    protected $fillable = [
        'ticket_id',
        'direction',
        'author_type',
        'author_user_id',
        'author_customer_contact_id',
        'channel',
        'is_internal',
        'body',
        'body_format',
        'delivery_state',
        'failure_reason',
        'failure_detail',
        'failed_at',
        'queued_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'external_message_id',
        'retry_count',
        'redacted_at',
        'ai_suggestion_id',
    ];

    protected $casts = [
        'direction' => MessageDirection::class,
        'author_type' => MessageAuthorType::class,
        'channel' => MessageChannel::class,
        'delivery_state' => MessageDeliveryState::class,
        'is_internal' => 'boolean',
        'retry_count' => 'integer',
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
        'redacted_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function authorContact(): BelongsTo
    {
        return $this->belongsTo(CustomerContact::class, 'author_customer_contact_id');
    }

    /**
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function deliveryEvents(): HasMany
    {
        return $this->hasMany(TicketMessageDeliveryEvent::class)->orderBy('occurred_at');
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(TicketMessageMention::class);
    }

    public function mentionedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_message_mentions', 'ticket_message_id', 'mentioned_user_id')
            ->withTimestamps();
    }

    public function scopeCustomerVisible(Builder $query): Builder
    {
        return $query->where('is_internal', false);
    }

    public function scopeFailedOutbound(Builder $query): Builder
    {
        return $query->where('direction', MessageDirection::Outbound)
            ->where('delivery_state', MessageDeliveryState::Failed);
    }

    public function isInternal(): bool
    {
        return $this->is_internal;
    }

    public function isOutbound(): bool
    {
        return $this->direction === MessageDirection::Outbound;
    }

    public function isRetryable(): bool
    {
        return $this->isOutbound() && $this->delivery_state === MessageDeliveryState::Failed;
    }
}
