<?php

namespace App\Domains\Ticketing\Models;

use App\Support\Models\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketCategoryField extends Model
{
    use GeneratesUuid;

    protected $guarded = ['*'];

    protected $fillable = ['ticket_category_id', 'key', 'label', 'type', 'options', 'is_required', 'position'];

    protected $casts = [
        'label' => 'array',
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }
}
