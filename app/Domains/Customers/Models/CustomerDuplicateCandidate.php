<?php

namespace App\Domains\Customers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDuplicateCandidate extends Model
{
    protected $guarded = ['*'];

    protected $fillable = ['customer_id', 'duplicate_customer_id', 'status', 'rule', 'evidence', 'reviewed_by_user_id', 'reviewed_at'];

    protected $casts = [
        'evidence' => 'array',
        'reviewed_at' => 'datetime',
        'status' => DuplicateCandidateStatus::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function duplicateCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'duplicate_customer_id');
    }
}
