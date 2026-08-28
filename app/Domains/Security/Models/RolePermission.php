<?php

namespace App\Domains\Security\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePermission extends Model
{
    protected $fillable = [
        'role_id',
        'permission_key',
    ];

    public $timestamps = true;

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
