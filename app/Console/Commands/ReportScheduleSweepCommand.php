<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Reporting\Jobs\GenerateReportExportJob;
use App\Domains\Reporting\Models\ReportSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class ReportScheduleSweepCommand extends Command
{
    protected $signature = 'report:schedule-sweep';

    protected $description = 'Sweep for due report schedules and dispatch generation jobs';

    public function handle(): int
    {
        $now = CarbonImmutable::now('UTC');

        $due = ReportSchedule::where('is_active', true)
            ->where('next_run_at', '<=', $now)
            ->orderBy('next_run_at')
            ->get();

        foreach ($due as $schedule) {
            // Lock the schedule to prevent double-dispatch
            $locked = ReportSchedule::where('id', $schedule->id)
                ->lockForUpdate()
                ->first();

            if (! $locked || ! $locked->is_active) {
                continue;
            }

            // Check if owner still has permission
            $owner = $locked->createdBy;
            if (! $owner || ! $owner->can('reports.view.'.$this->getScope($locked->filters))) {
                $locked->update(['is_active' => false]);

                continue;
            }

            // Dispatch job
            GenerateReportExportJob::dispatch(
                $locked->toExport()
            );

            // Advance next_run_at
            $locked->update([
                'last_run_at' => $now,
                'next_run_at' => $this->computeNextRun($locked),
            ]);

            $this->line("Dispatched schedule {$locked->name}");
        }

        return 0;
    }

    private function computeNextRun(ReportSchedule $schedule): CarbonImmutable
    {
        return $schedule->computeNextRun();
    }

    private function getScope(array $filters): string
    {
        return $filters['branchId'] ? 'branch' : ($filters['departmentId'] ? 'department' : 'any');
    }
}
