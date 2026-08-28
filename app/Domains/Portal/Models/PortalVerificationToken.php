<?php

namespace App\Domains\Portal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalVerificationToken extends Model
{
    protected $fillable = [
        'portal_account_id',
        'contact_channel',
        'contact_value_hash',
        'token_hash',
        'expires_at',
        'consumed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(PortalAccount::class, 'portal_account_id');
    }
}
