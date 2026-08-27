<?php

namespace App\Domains\Ticketing\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessageMention extends Model
{
    protected $guarded = ['*'];

    protected $fillable = ['ticket_message_id', 'mentioned_user_id'];

    public const UPDATED_AT = null;

    public function ticketMessage(): BelongsTo
    {
        return $this->belongsTo(TicketMessage::class);
    }

    public function mentionedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioned_user_id');
    }
}
