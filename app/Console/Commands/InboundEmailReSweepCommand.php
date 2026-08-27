<?php

namespace App\Console\Commands;

use App\Domains\Channels\Email\Jobs\ProcessInboundEmailJob;
use App\Domains\Channels\Email\Models\InboundEmailMessage;
use App\Domains\Channels\Email\Models\InboundState;
use Illuminate\Console\Command;

class InboundEmailReSweepCommand extends Command
{
    protected $signature = 'email:resweep';

    protected $description = 'Resweep inbound email messages stuck in received state';

    public function handle(): int
    {
        $count = 0;
        $stuckMinutes = 10;

        $rows = InboundEmailMessage::where('state', InboundState::Received->value)
            ->where('created_at', '<', now()->subMinutes($stuckMinutes))
            ->limit(100)
            ->get();

        foreach ($rows as $row) {
            ProcessInboundEmailJob::dispatch($row->id)->onQueue('email');
            $count++;
        }

        if ($count > 0) {
            $this->info("Rescheduled {$count} stuck inbound emails");
        }

        return 0;
    }
}
