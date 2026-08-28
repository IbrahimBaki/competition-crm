<?php

namespace App\Domains\Security\Models;

use App\Domains\Security\Exceptions\AuditLogImmutableException;
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

    protected static function booted(): void
    {
        static::updating(fn () => throw new AuditLogImmutableException);
        static::deleting(fn () => throw new AuditLogImmutableException);
    }
}
