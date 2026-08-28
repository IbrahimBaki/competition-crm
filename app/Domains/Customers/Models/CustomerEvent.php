<?php

namespace App\Domains\Customers\Models;

use App\Models\User;
use App\Support\Models\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerEvent extends Model
{
    use GeneratesUuid;

    protected $guarded = ['*'];

    protected $fillable = ['type', 'payload', 'occurred_at'];

    protected $casts = [
        'type' => CustomerEventType::class,
        'payload' => 'json',
        'occurred_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
