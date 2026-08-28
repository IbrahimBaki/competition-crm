<?php

namespace App\Domains\Sla\Models;

use App\Domains\Sla\Exceptions\SlaBreachImmutableException;
use App\Domains\Ticketing\Models\Ticket;
use App\Support\Models\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaBreach extends Model
{
    use GeneratesUuid;

    const UPDATED_AT = null;

    protected $guarded = ['*'];

    protected $fillable = [
        'ticket_id',
        'ticket_sla_clock_id',
        'target_type',
        'priority',
        'target_minutes',
        'due_at',
        'breached_at',
        'actual_minutes',
        'overdue_minutes',
        'reason',
    ];

    protected $casts = [
        'target_type' => SlaTargetType::class,
        'reason' => SlaBreachReason::class,
        'due_at' => 'immutable_datetime',
        'breached_at' => 'immutable_datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function clock(): BelongsTo
    {
        return $this->belongsTo(TicketSlaClock::class, 'ticket_sla_clock_id');
    }

    public function save(array $options = []): bool
    {
        if ($this->exists) {
            throw new SlaBreachImmutableException('SLA breaches are append-only and cannot be modified.');
        }

        return parent::save($options);
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        throw new SlaBreachImmutableException('SLA breaches are append-only and cannot be modified.');
    }

    public function delete(): ?bool
    {
        throw new SlaBreachImmutableException('SLA breaches are append-only and cannot be deleted.');
    }
}
