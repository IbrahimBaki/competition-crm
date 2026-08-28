<?php

namespace App\Domains\Channels\Email\Models;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InboundEmailMessage extends Model
{
    protected $table = 'inbound_email_messages';

    protected $fillable = [
        'uuid',
        'provider',
        'provider_event_id',
        'message_id',
        'from_address',
        'to_address',
        'subject',
        'raw_path',
        'classification',
        'state',
        'attempts',
        'last_error',
        'ticket_id',
        'ticket_message_id',
        'received_at',
        'processed_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
        'classification' => InboundClassification::class,
        'state' => InboundState::class,
    ];

    protected static function booting(): void
    {
        static::creating(function (self $model) {
            $model->uuid = $model->uuid ?? Str::uuid();
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function ticketMessage(): BelongsTo
    {
        return $this->belongsTo(TicketMessage::class);
    }
}
