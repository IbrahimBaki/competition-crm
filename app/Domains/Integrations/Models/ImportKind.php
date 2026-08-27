<?php

namespace App\Domains\Integrations\Models;

enum ImportKind: string
{
    case Customers = 'customers';
    case HistoricalTickets = 'historical_tickets';
}
