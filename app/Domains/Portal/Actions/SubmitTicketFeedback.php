<?php

namespace App\Domains\Portal\Actions;

use App\Domains\Customers\Models\Customer;
use App\Domains\Portal\Exceptions\TicketFeedbackAlreadySubmittedException;
use App\Domains\Portal\Models\TicketFeedback;
use App\Domains\Ticketing\Models\Ticket;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class SubmitTicketFeedback
{
    public function handle(Ticket $ticket, int $score, ?string $comment, ?Customer $customer, string $source): TicketFeedback
    {
        try {
            return TicketFeedback::create([
                'uuid' => (string) Str::uuid(),
                'ticket_id' => $ticket->id,
                'customer_id' => $customer?->id,
                'score' => $score,
                'comment' => $comment,
                'submitted_at' => now(),
                'source' => $source,
            ]);
        } catch (QueryException $e) {
            if (strpos($e->getMessage(), 'Unique constraint failed') !== false) {
                throw new TicketFeedbackAlreadySubmittedException;
            }

            throw $e;
        }
    }
}
