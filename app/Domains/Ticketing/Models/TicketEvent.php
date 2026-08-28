<?php

namespace App\Domains\Ticketing\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketEvent extends Model
{
    protected $guarded = ['*'];

    protected $fillable = ['ticket_id', 'type', 'actor_user_id', 'payload', 'occurred_at'];

    protected $casts = [
        'type' => TicketEventType::class,
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
