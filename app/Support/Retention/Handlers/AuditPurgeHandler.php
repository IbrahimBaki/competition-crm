<?php

namespace App\Support\Retention\Handlers;

use App\Domains\Security\Exceptions\AuditRetentionWindowTooShortException;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;
use Illuminate\Support\Facades\DB;

class AuditPurgeHandler implements PurgeHandler
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function dataClass(): string
    {
        return 'audit';
    }

    public function purge(RetentionPolicy $policy): int
    {
        if ($policy->days === null) {
            return 0;
        }

        $configuredDays = $policy->days;
        $minimumDays = config('retention.audit_minimum_days', 365);

        if ($configuredDays < $minimumDays) {
            throw new AuditRetentionWindowTooShortException(
                "Audit retention window ({$configuredDays} days) is shorter than the minimum ({$minimumDays} days)"
            );
        }

        $batchSize = config('retention.batch_size', 500);
        $totalDeleted = 0;

        $eligibleCount = DB::table('audit_logs')
            ->where('recorded_at', '<', $policy->cutoff)
            ->count();

        if ($eligibleCount === 0) {
            return 0;
        }

        $oldestEligible = DB::table('audit_logs')
            ->where('recorded_at', '<', $policy->cutoff)
            ->orderBy('recorded_at')
            ->value('recorded_at');

        $newestEligible = DB::table('audit_logs')
            ->where('recorded_at', '<', $policy->cutoff)
            ->orderByDesc('recorded_at')
            ->value('recorded_at');

        $systemUser = User::query()
            ->where('email', 'system@internal')
            ->firstOrCreate(['uuid' => 'system-user'], [
                'name' => 'System',
                'email' => 'system@internal',
                'password' => '',
            ]);

        $this->auditLogger->record(
            $systemUser,
            'audit.purge.executed',
            $systemUser,
            null,
            [
                'cutoff' => $policy->cutoff->toIso8601String(),
                'eligible_count' => $eligibleCount,
                'oldest_eligible' => $oldestEligible,
                'newest_eligible' => $newestEligible,
            ]
        );

        while (true) {
            $deleted = DB::table('audit_logs')
                ->where('recorded_at', '<', $policy->cutoff)
                ->limit($batchSize)
                ->delete();

            if ($deleted === 0) {
                break;
            }

            $totalDeleted += $deleted;
        }

        return $totalDeleted;
    }
}
