<?php

namespace App\Domains\Channels\Messaging\Models;

use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProviderInboundMessage extends Model
{
    protected $fillable = [
        'uuid',
        'channel',
        'provider_message_id',
        'from_identifier',
        'raw_payload',
        'state',
        'ticket_id',
        'received_at',
    ];

    protected $casts = [
        'channel' => MessageChannel::class,
        'raw_payload' => 'json',
        'received_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (! $model->uuid) {
                $model->uuid = Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
