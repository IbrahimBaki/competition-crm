<?php

namespace App\Domains\Channels\WebForm\Models;

use App\Domains\Customers\Models\Customer;
use App\Domains\Ticketing\Models\Ticket;
use App\Support\Models\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebFormSubmission extends Model
{
    use GeneratesUuid;

    protected $fillable = ['web_form_id', 'ticket_id', 'customer_id', 'state', 'fingerprint', 'tracking_token', 'payload', 'submitter_ip_hash', 'user_agent', 'duplicate_of_id'];

    protected $casts = [
        'payload' => 'array',
        'state' => WebFormSubmissionState::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(WebForm::class, 'web_form_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(WebFormSubmission::class, 'duplicate_of_id');
    }
}
