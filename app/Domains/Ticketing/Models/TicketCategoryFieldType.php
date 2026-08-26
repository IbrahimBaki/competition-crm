<?php

namespace App\Domains\Ticketing\Models;

enum TicketCategoryFieldType: string
{
    case Text = 'text';
    case Number = 'number';
    case Date = 'date';
    case Select = 'select';
    case Boolean = 'boolean';
}
