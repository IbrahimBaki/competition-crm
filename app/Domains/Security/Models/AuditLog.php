<?php

namespace App\Domains\Security\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['id', 'actor_uuid', 'action', 'target_type', 'target_id', 'before', 'after', 'recorded_at'];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
