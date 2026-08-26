<?php

namespace App\Domains\Sla\Models;

use App\Domains\Ticketing\Models\TicketCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaTarget extends Model
{
    protected $guarded = ['*'];

    protected $fillable = [
        'uuid',
        'sla_policy_id',
        'target_type',
        'priority',
        'ticket_category_id',
        'service_tier',
        'minutes',
    ];

    protected $casts = [
        'target_type' => SlaTargetType::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class, 'sla_policy_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }
}
