<?php

namespace App\Domains\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessageDeliveryEvent extends Model
{
    protected $guarded = ['*'];

    protected $fillable = [
        'ticket_message_id',
        'from_state',
        'to_state',
        'reason',
        'detail',
        'occurred_at',
    ];

    protected $casts = [
        'from_state' => MessageDeliveryState::class,
        'to_state' => MessageDeliveryState::class,
        'occurred_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(TicketMessage::class, 'ticket_message_id');
    }
}
