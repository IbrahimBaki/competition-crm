<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketWatcher;
use App\Models\User;

readonly class WatchTicket
{
    public function handle(Ticket $ticket, User $user): void
    {
        TicketWatcher::updateOrCreate([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
        ]);
    }
}
