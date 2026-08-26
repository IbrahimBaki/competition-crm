<?php

namespace App\Domains\Customers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerContact extends Model
{
    protected $guarded = ['*'];

    protected $fillable = ['type', 'value', 'label', 'is_primary'];

    protected $casts = [
        'type' => ContactType::class,
        'verified_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
