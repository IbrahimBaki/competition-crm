<?php

namespace App\Domains\Security\Models;

use Illuminate\Database\Eloquent\Model;

class UserInvitation extends Model
{
    protected $fillable = [
        'id',
        'email',
        'invited_by_uuid',
        'token_hash',
        'expires_at',
        'accepted_at',
        'accepted_user_uuid',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
