<?php

namespace App\Domains\Customers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerContact extends Model
{
    protected $guarded = ['*'];

    protected $fillable = [
        'type',
        'value',
        'label',
        'is_primary',
        'whatsapp_opted_in_at',
        'whatsapp_opt_in_source',
        'sms_opted_out_at',
        'sms_opt_out_source',
    ];

    protected $casts = [
        'type' => ContactType::class,
        'verified_at' => 'datetime',
        'whatsapp_opted_in_at' => 'datetime',
        'sms_opted_out_at' => 'datetime',
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
