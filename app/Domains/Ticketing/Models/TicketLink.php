<?php

namespace App\Domains\Ticketing\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketLink extends Model
{
    protected $guarded = ['*'];

    protected $fillable = ['source_ticket_id', 'target_ticket_id', 'relation', 'created_by_user_id'];

    protected $casts = [
        'relation' => TicketLinkRelation::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'source_ticket_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'target_ticket_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
