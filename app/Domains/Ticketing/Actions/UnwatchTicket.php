<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Exceptions\TicketNotWatchedException;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;

readonly class UnwatchTicket
{
    public function handle(Ticket $ticket, User $user): void
    {
        if (!$ticket->watchers()->where('user_id', $user->id)->exists()) {
            throw new TicketNotWatchedException();
        }
        $ticket->watchers()->detach($user->id);
    }
}
