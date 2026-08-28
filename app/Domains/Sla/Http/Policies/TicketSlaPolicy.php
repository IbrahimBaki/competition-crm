<?php

namespace App\Domains\Sla\Http\Policies;

use App\Domains\Sla\Models\TicketSlaClock;
use App\Models\User;

class TicketSlaPolicy
{
    public function view(User $user, TicketSlaClock $clock): bool
    {
        return $user->can('tickets.view.department');
    }

    public function reset(User $user, TicketSlaClock $clock): bool
    {
        return $user->can('sla.reset');
    }
}
