<?php

namespace App\Domains\Ticketing\Models;

use App\Support\Models\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TicketTag extends Model
{
    use GeneratesUuid;
    use HasFactory;

    protected $guarded = ['*'];

    protected $fillable = ['name', 'name_normalised'];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_tag_ticket')
            ->withTimestamps();
    }
}
