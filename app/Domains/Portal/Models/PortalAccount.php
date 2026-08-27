<?php

namespace App\Domains\Portal\Models;

use App\Domains\Customers\Models\Customer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class PortalAccount extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'uuid',
        'customer_id',
        'email',
        'password',
        'locale',
        'email_verified_at',
        'last_login_at',
        'failed_login_attempts',
        'locked_until',
        'deactivated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'locked_until' => 'datetime',
        'deactivated_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function portal_verification_tokens()
    {
        return $this->hasMany(PortalVerificationToken::class);
    }
}
