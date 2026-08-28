<?php

namespace App\Domains\Portal\Models;

use App\Domains\Channels\WebForm\Models\WebFormSubmission;
use App\Domains\Ticketing\Models\Ticket;
use App\Support\Models\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalGuestGrant extends Model
{
    use GeneratesUuid;

    protected $fillable = [
        'uuid',
        'token_hash',
        'ticket_id',
        'web_form_submission_id',
        'expires_at',
        'revoked_at',
        'last_used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function webFormSubmission(): BelongsTo
    {
        return $this->belongsTo(WebFormSubmission::class);
    }
}
