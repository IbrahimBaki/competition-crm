<?php

namespace App\Console\Commands;

use App\Domains\Ticketing\Actions\ChangeTicketStatus;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use Illuminate\Console\Command;

class CloseExpiredTicketsCommand extends Command
{
    protected $signature = 'tickets:close-expired';

    protected $description = 'Close resolved tickets that have exceeded the reopen window';

    public function __construct(
        private readonly ChangeTicketStatus $changeStatus,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $closedStatus = TicketStatusDefinition::query()
            ->where('lifecycle_type', 'closed')
            ->firstOrFail();

        $expiredTickets = Ticket::query()
            ->whereNotNull('reopen_deadline_at')
            ->where('reopen_deadline_at', '<', now())
            ->chunkById(500, function ($tickets) use ($closedStatus) {
                foreach ($tickets as $ticket) {
                    try {
                        ($this->changeStatus)(
                            $ticket,
                            $closedStatus,
                            null,
                            'ticket.reopen_window_expired',
                        );

                        $this->info("Closed ticket: {$ticket->uuid}");
                    } catch (\Exception $e) {
                        $this->error("Failed to close ticket {$ticket->uuid}: {$e->getMessage()}");
                    }
                }
            });

        return 0;
    }
}
