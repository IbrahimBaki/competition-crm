<?php

namespace App\Domains\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per "YYYY-MM" period, holding the last-allocated ticket sequence
 * number for that period. See TicketReferenceGenerator — this model has no
 * uuid/route-key concerns since it is never exposed over the API.
 */
class TicketReference extends Model
{
    protected $fillable = ['period', 'last_sequence'];

    protected $casts = [
        'last_sequence' => 'integer',
    ];
}
