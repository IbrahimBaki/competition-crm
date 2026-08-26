<?php

namespace App\Domains\Sla\Models;

use App\Domains\Ticketing\Models\TicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaPauseInterval extends Model
{
    protected $guarded = ['*'];

    protected $fillable = [
        'ticket_sla_clock_id',
        'ticket_status_id',
        'paused_at',
        'resumed_at',
        'paused_working_minutes',
        'reason',
    ];

    protected $casts = [
        'paused_at' => 'immutable_datetime',
        'resumed_at' => 'immutable_datetime',
    ];

    public function clock(): BelongsTo
    {
        return $this->belongsTo(TicketSlaClock::class, 'ticket_sla_clock_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class, 'ticket_status_id');
    }
}
