<?php

namespace App\Domains\Ticketing\Models;

use App\Domains\Customers\Models\Customer;
use App\Domains\Organisation\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $guarded = ['*'];

    protected $fillable = [
        'subject',
        'body',
        'priority',
        'department_id',
        'ticket_category_id',
        'ticket_status_id',
        'status_changed_at',
        'resolved_at',
        'reopen_deadline_at',
        'reopened_count',
        'merged_into_ticket_id',
        'merged_at',
        'parent_ticket_id',
        'spam_marked_at',
    ];

    protected $casts = [
        'status' => TicketStatus::class,
        'priority' => TicketPriority::class,
        'custom_fields' => 'array',
        'status_changed_at' => 'datetime',
        'resolved_at' => 'datetime',
        'reopen_deadline_at' => 'datetime',
        'reopened_count' => 'integer',
        'merged_at' => 'datetime',
        'spam_marked_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatusDefinition::class, 'ticket_status_id');
    }

    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'merged_into_ticket_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'parent_ticket_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Ticket::class, 'parent_ticket_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TicketTag::class, 'ticket_tag_ticket')
            ->withTimestamps();
    }

    public function events(): HasMany
    {
        return $this->hasMany(TicketEvent::class)->orderBy('occurred_at', 'desc');
    }

    public function links(): HasMany
    {
        return $this->hasMany(TicketLink::class, 'source_ticket_id');
    }

    public function linkedFrom(): HasMany
    {
        return $this->hasMany(TicketLink::class, 'target_ticket_id');
    }

    public function lifecycleType(): TicketStatus
    {
        return $this->status?->lifecycle_type ?? TicketStatus::New;
    }

    public function isMerged(): bool
    {
        return $this->merged_into_ticket_id !== null;
    }

    public function isSpam(): bool
    {
        return $this->lifecycleType() === TicketStatus::Spam;
    }
}
