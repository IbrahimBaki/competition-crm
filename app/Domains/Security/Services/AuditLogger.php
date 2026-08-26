<?php

namespace App\Domains\Security\Services;

use App\Domains\Security\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLogger
{
    public function record(?Authenticatable $actor, string $action, Model $target, ?array $before, ?array $after): AuditLog
    {
        $actorUuid = null;
        if ($actor !== null) {
            $actorUuid = $actor instanceof User ? $actor->uuid : null;
        }

        return AuditLog::create([
            'id' => Str::uuid(),
            'actor_uuid' => $actorUuid,
            'action' => $action,
            'target_type' => class_basename($target),
            'target_id' => (string) $target->getKey(),
            'before' => $before,
            'after' => $after,
            'recorded_at' => now(),
        ]);
    }
}
