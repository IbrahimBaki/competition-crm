<?php

namespace App\Domains\Portal\Models;

use App\Domains\Ticketing\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketFeedbackInvitation extends Model
{
    protected $fillable = [
        'ticket_id',
        'token_hash',
        'expires_at',
        'consumed_at',
        'sent_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
