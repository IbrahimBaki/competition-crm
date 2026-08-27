<?php

namespace App\Domains\Integrations\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ApiToken extends Model
{
    protected $fillable = ['name', 'token_hash', 'token_prefix', 'scopes', 'expires_at', 'created_by_user_id'];

    protected $hidden = ['token_hash', 'id'];

    protected $casts = [
        'scopes' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    public function isActive(): bool
    {
        if ($this->revoked_at !== null) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function hasScope(ApiScope $scope): bool
    {
        return in_array($scope->value, $this->scopes ?? [], true);
    }

    public function touchLastUsedAt(): void
    {
        if ($this->last_used_at === null || $this->last_used_at->diffInSeconds(now()) >= 60) {
            $this->update(['last_used_at' => now()]);
        }
    }
}
