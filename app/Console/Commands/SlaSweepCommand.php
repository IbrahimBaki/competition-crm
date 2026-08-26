<?php

namespace App\Console\Commands;

use App\Domains\Sla\Models\SlaClockState;
use App\Domains\Sla\Models\TicketSlaClock;
use App\Domains\Sla\Services\SlaClockService;
use App\Domains\Sla\Services\SlaEvaluator;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class SlaSweepCommand extends Command
{
    protected $signature = 'sla:sweep {--limit=500}';

    protected $description = 'Process SLA clock warnings and breaches for overdue running clocks';

    public function __construct(
        private readonly SlaClockService $clockService,
        private readonly SlaEvaluator $evaluator,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $now = CarbonImmutable::now('UTC');

        $clocks = TicketSlaClock::where('state', SlaClockState::Running->value)
            ->where(function ($query) use ($now) {
                $query->where('due_at', '<=', $now)
                    ->orWhereNull('warned_at');
            })
            ->orderBy('due_at')
            ->limit($limit)
            ->lockForUpdate()
            ->get();

        $processed = 0;
        foreach ($clocks as $clock) {
            $this->clockService->accrue($clock, $now);
            $this->evaluator->evaluate($clock, $now);
            $processed++;
        }

        $this->info("Processed {$processed} SLA clocks");

        return self::SUCCESS;
    }
}
