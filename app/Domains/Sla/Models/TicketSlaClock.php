<?php

namespace App\Domains\Sla\Models;

use App\Domains\Ticketing\Models\Ticket;
use App\Support\Models\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TicketSlaClock extends Model
{
    use GeneratesUuid;

    protected $guarded = ['*'];

    protected $fillable = [
        'ticket_id',
        'sla_policy_id',
        'sla_target_id',
        'target_type',
        'target_minutes',
        'started_at',
        'elapsed_minutes',
        'last_counted_at',
        'due_at',
        'state',
        'paused_at',
        'warned_at',
        'completed_at',
        'breached_at',
        'reset_at',
    ];

    protected $casts = [
        'target_type' => SlaTargetType::class,
        'state' => SlaClockState::class,
        'started_at' => 'immutable_datetime',
        'last_counted_at' => 'immutable_datetime',
        'due_at' => 'immutable_datetime',
        'paused_at' => 'immutable_datetime',
        'warned_at' => 'immutable_datetime',
        'completed_at' => 'immutable_datetime',
        'breached_at' => 'immutable_datetime',
        'reset_at' => 'immutable_datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class, 'sla_policy_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(SlaTarget::class, 'sla_target_id');
    }

    public function pauseIntervals(): HasMany
    {
        return $this->hasMany(SlaPauseInterval::class, 'ticket_sla_clock_id');
    }

    public function breach(): HasOne
    {
        return $this->hasOne(SlaBreach::class, 'ticket_sla_clock_id');
    }
}
