<?php

namespace App\Domains\Ticketing\Models;

use App\Support\Models\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessageDeliveryEvent extends Model
{
    use GeneratesUuid;

    // Append-only log: the table records `occurred_at` and has no
    // created_at/updated_at columns.
    public $timestamps = false;

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
