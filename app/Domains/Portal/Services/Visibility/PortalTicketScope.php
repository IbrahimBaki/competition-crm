<?php

namespace App\Domains\Portal\Services\Visibility;

use App\Domains\Portal\Models\PortalAccount;
use App\Domains\Ticketing\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;

class PortalTicketScope
{
    public function forAccount(PortalAccount $account): Builder
    {
        return Ticket::query()
            ->where('customer_id', $account->customer_id)
            ->whereNotIn('status', ['spam', 'merged']);
    }

    public function findOrFail(PortalAccount $account, string $uuid): Ticket
    {
        return $this->forAccount($account)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }
}
