<?php

namespace App\Domains\Portal\Models;

use App\Domains\Customers\Models\Customer;
use App\Domains\Ticketing\Models\Ticket;
use App\Support\Models\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketFeedback extends Model
{
    use GeneratesUuid;

    protected $fillable = [
        'uuid',
        'ticket_id',
        'customer_id',
        'score',
        'comment',
        'submitted_at',
        'source',
    ];

    protected $casts = [
        'score' => 'integer',
        'submitted_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
