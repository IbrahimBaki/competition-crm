<?php

namespace App\Domains\Ticketing\Policies;

use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketMessage;
use App\Models\User;

class TicketMessagePolicy
{
    public function __construct(
        private readonly TicketPolicy $ticketPolicy,
    ) {}

    public function viewAny(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo(PermissionKey::TICKET_MESSAGE_VIEW)
            && $this->ticketPolicy->view($user, $ticket);
    }

    public function viewInternal(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo(PermissionKey::TICKET_MESSAGE_INTERNAL_VIEW);
    }

    public function send(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo(PermissionKey::TICKET_MESSAGE_SEND);
    }

    public function writeInternal(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo(PermissionKey::TICKET_MESSAGE_INTERNAL_WRITE);
    }

    public function retry(User $user, TicketMessage $message): bool
    {
        return $user->hasPermissionTo(PermissionKey::TICKET_MESSAGE_RETRY)
            && $message->isRetryable();
    }
}
