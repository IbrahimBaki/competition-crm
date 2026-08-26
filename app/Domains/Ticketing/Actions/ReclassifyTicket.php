<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketCategory;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Domains\Ticketing\Services\TicketCategoryTree;
use App\Domains\Ticketing\Services\TicketCustomFieldValidator;
use App\Models\User;

class ReclassifyTicket
{
    public function __construct(
        private readonly TicketCategoryTree $categoryTree,
        private readonly TicketCustomFieldValidator $fieldValidator,
        private readonly RecordTicketEvent $recordEvent,
    ) {}

    public function handle(
        Ticket $ticket,
        TicketCategory $newCategory,
        ?User $actor = null,
    ): Ticket {
        $oldCategory = $ticket->category;
        $oldFields = $ticket->custom_fields ?? [];

        $ticket->update(['ticket_category_id' => $newCategory->id]);

        $newFields = $this->fieldValidator->validate($newCategory, $oldFields);
        $ticket->update(['custom_fields' => $newFields ?: null]);

        $this->recordEvent->handle($ticket, TicketEventType::Reclassified, $actor, [
            'old_category_uuid' => $oldCategory?->uuid,
            'new_category_uuid' => $newCategory->uuid,
            'orphaned_fields' => array_diff_key($oldFields, $newFields),
        ]);

        return $ticket->fresh();
    }
}
