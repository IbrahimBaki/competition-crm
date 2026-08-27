<?php

namespace App\Domains\Channels\Chat\Models;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerContact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChatVisitorIdentity extends Model
{
    protected $fillable = [
        'visitor_token',
        'customer_id',
        'customer_contact_id',
        'display_name',
        'email',
        'phone',
        'normalised_email',
        'normalised_phone',
        'locale',
        'user_agent',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->uuid ??= Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerContact(): BelongsTo
    {
        return $this->belongsTo(CustomerContact::class);
    }

    public function chatSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class);
    }
}
