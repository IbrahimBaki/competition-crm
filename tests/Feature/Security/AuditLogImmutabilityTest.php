<?php

namespace Tests\Feature\Security;

use App\Domains\Security\Exceptions\AuditLogImmutableException;
use App\Domains\Security\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_cannot_be_updated(): void
    {
        $log = AuditLog::create([
            'id' => 'test-id-1',
            'actor_uuid' => 'actor-uuid',
            'action' => 'test.action',
            'target_type' => 'User',
            'target_id' => 'user-1',
            'before' => null,
            'after' => ['name' => 'Test'],
            'recorded_at' => now(),
        ]);

        $this->expectException(AuditLogImmutableException::class);
        $log->update(['action' => 'modified']);
    }

    public function test_audit_log_cannot_be_deleted(): void
    {
        $log = AuditLog::create([
            'id' => 'test-id-2',
            'actor_uuid' => 'actor-uuid',
            'action' => 'test.action',
            'target_type' => 'User',
            'target_id' => 'user-1',
            'before' => null,
            'after' => ['name' => 'Test'],
            'recorded_at' => now(),
        ]);

        $this->expectException(AuditLogImmutableException::class);
        $log->delete();
    }
}
