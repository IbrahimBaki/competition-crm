<?php

namespace App\Console\Commands;

use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Automation\Models\RuleTrigger;
use App\Domains\Automation\Services\RuleEngine;
use App\Domains\Ticketing\Models\Ticket;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class AutomationSweepCommand extends Command
{
    protected $signature = 'automation:sweep {--limit=500} {--rule=}';

    protected $description = 'Evaluate scheduled automation rules (approaching breach, stale tickets, auto-close)';

    public function __construct(
        private readonly RuleEngine $engine,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $now = CarbonImmutable::now('UTC');

        $rules = AutomationRule::where('trigger', RuleTrigger::Scheduled->value)
            ->where('is_active', true)
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $processed = 0;

        foreach ($rules as $rule) {
            $tickets = Ticket::where('is_deleted', false)
                ->limit($limit)
                ->lockForUpdate()
                ->get();

            foreach ($tickets as $ticket) {
                $this->engine->run(RuleTrigger::Scheduled, $ticket);
                $processed++;
            }
        }

        $this->info("Processed {$processed} tickets across {$rules->count()} rules");

        return self::SUCCESS;
    }
}
