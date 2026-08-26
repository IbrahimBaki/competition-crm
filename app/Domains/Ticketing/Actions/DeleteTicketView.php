<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Models\TicketSavedView;

class DeleteTicketView
{
    public function handle(TicketSavedView $view): void
    {
        $view->delete();
    }
}
