<?php

namespace App\Domains\Customers\Models;

use App\Models\User;
use App\Support\Attachments\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Customer extends Model
{
    protected $guarded = ['*'];

    protected $fillable = ['name', 'company_account_id', 'preferred_locale'];

    protected $casts = [
        'status' => CustomerStatus::class,
        'blocked_at' => 'datetime',
        'anonymised_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function companyAccount(): BelongsTo
    {
        return $this->belongsTo(CompanyAccount::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(CustomerEvent::class)->orderBy('occurred_at', 'desc');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function blockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by_user_id');
    }

    public function isBlocked(): bool
    {
        return $this->status === CustomerStatus::Blocked;
    }
}
