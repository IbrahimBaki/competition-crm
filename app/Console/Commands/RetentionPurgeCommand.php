<?php

namespace App\Console\Commands;

use App\Domains\Security\Exceptions\AuditRetentionWindowTooShortException;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use App\Support\Retention\RetentionPolicy;
use App\Support\Retention\RetentionRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RetentionPurgeCommand extends Command
{
    protected $signature = 'retention:purge {--class=* : Limit to specific data classes} {--dry-run : Preview what would be deleted without actually deleting}';

    protected $description = 'Purge data according to configured retention policies';

    public function __construct(
        private RetentionRegistry $registry,
        private AuditLogger $auditLogger,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run') || config('retention.dry_run', false);
        $classFilter = $this->option('class');

        $configuredClasses = array_keys(config('retention.classes', []));

        if (! empty($classFilter)) {
            $classesToProcess = array_intersect($classFilter, $configuredClasses);
            if (count($classesToProcess) !== count($classFilter)) {
                $invalid = array_diff($classFilter, $configuredClasses);
                $this->error('Unknown data classes: '.implode(', ', $invalid));

                return 1;
            }
        } else {
            $classesToProcess = $configuredClasses;
        }

        foreach ($classesToProcess as $dataClass) {
            if (! $this->registry->has($dataClass)) {
                $this->error("No purge handler registered for data class: {$dataClass}");

                return 1;
            }
        }

        $results = [];
        $hasFailures = false;

        foreach ($classesToProcess as $dataClass) {
            $config = config("retention.classes.{$dataClass}");
            $policy = RetentionPolicy::fromConfig($dataClass, $config);
            $handler = $this->registry->get($dataClass);

            $skipReason = null;

            try {
                if ($dryRun) {
                    $this->line("<fg=yellow>DRY RUN:</> {$dataClass}: would delete records older than {$policy->cutoff->toDateTimeString()}");
                    $deleted = 0;
                } else {
                    $deleted = DB::transaction(fn () => $handler->purge($policy));
                }

                $results[$dataClass] = [
                    'days' => $policy->days,
                    'cutoff' => $policy->cutoff->toIso8601String(),
                    'deleted' => $deleted,
                    'skipped' => 0,
                    'skip_reason' => null,
                    'dry_run' => $dryRun,
                ];

                $this->line("<fg=green>✓</> {$dataClass}: deleted {$deleted} record".($deleted === 1 ? '' : 's'));
            } catch (AuditRetentionWindowTooShortException $e) {
                $this->error("✗ {$dataClass}: {$e->getMessage()}");
                $results[$dataClass] = [
                    'days' => $policy->days,
                    'cutoff' => $policy->cutoff->toIso8601String(),
                    'deleted' => 0,
                    'skipped' => 0,
                    'skip_reason' => 'configured window shorter than minimum',
                    'dry_run' => $dryRun,
                ];
                $hasFailures = true;
            } catch (\Exception $e) {
                $this->error("✗ {$dataClass}: {$e->getMessage()}");
                $results[$dataClass] = [
                    'days' => $policy->days,
                    'cutoff' => $policy->cutoff->toIso8601String(),
                    'deleted' => 0,
                    'skipped' => 0,
                    'skip_reason' => $e->getMessage(),
                    'dry_run' => $dryRun,
                ];
                $hasFailures = true;
            }
        }

        if (! $dryRun && ! empty($results)) {
            $systemUser = User::query()
                ->where('email', 'system@internal')
                ->first();

            if ($systemUser) {
                $this->auditLogger->record(
                    $systemUser,
                    'retention.purge.completed',
                    $systemUser,
                    null,
                    $results,
                );
            }
        }

        return $hasFailures ? 1 : 0;
    }
}
