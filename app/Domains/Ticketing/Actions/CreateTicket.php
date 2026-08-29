<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Services\CustomerIntakeGuard;
use App\Domains\Customers\Services\TextNormaliser;
use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketCategory;
use App\Domains\Ticketing\Models\TicketEventType;
use App\Domains\Ticketing\Models\TicketPriority;
use App\Domains\Ticketing\Models\TicketStatus;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Domains\Ticketing\Services\Automation\TicketAutomationHooks;
use App\Domains\Ticketing\Services\RecordTicketEvent;
use App\Domains\Ticketing\Services\Sla\SlaClockHooks;
use App\Domains\Ticketing\Services\TicketCategoryTree;
use App\Domains\Ticketing\Services\TicketCustomFieldValidator;
use App\Domains\Ticketing\Services\TicketReferenceGenerator;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTicket
{
    public function __construct(
        private readonly CustomerIntakeGuard $intakeGuard,
        private readonly TicketReferenceGenerator $referenceGenerator,
        private readonly TicketCategoryTree $categoryTree,
        private readonly TicketCustomFieldValidator $fieldValidator,
        private readonly RecordTicketEvent $recordEvent,
        private readonly SyncTicketTags $syncTags,
        private readonly SlaClockHooks $slaHooks,
        private readonly TicketAutomationHooks $automationHooks,
    ) {}

    public function handle(
        Customer $customer,
        Department $department,
        TicketPriority $priority,
        string $subject,
        string $body,
        ?TicketCategory $category = null,
        ?User $assignee = null,
        ?array $tags = null,
        ?array $customFields = null,
        ?User $actor = null,
    ): Ticket {
        return DB::transaction(function () use (
            $customer,
            $department,
            $priority,
            $subject,
            $body,
            $category,
            $assignee,
            $tags,
            $customFields,
            $actor,
        ) {
            $this->intakeGuard->assertCanOpenTicket($customer);

            $reference = $this->referenceGenerator->next();
            $validatedFields = $this->fieldValidator->validate($category, $customFields);

            $normaliser = app(TextNormaliser::class);
            $defaultStatus = TicketStatusDefinition::query()
                ->where('lifecycle_type', TicketStatus::New->value)
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->orderBy('position')
                ->first();

            $ticket = Ticket::create([
                'customer_id' => $customer->id,
                'department_id' => $department->id,
                'ticket_category_id' => $category?->id,
                'assigned_user_id' => $assignee?->id,
                'created_by_user_id' => $actor?->id,
                'reference' => $reference,
                'subject' => $subject,
                'subject_normalised' => $normaliser->normaliseName($subject),
                'body' => $body,
                'body_normalised' => $normaliser->normaliseName($body),
                'status' => TicketStatus::New,
                'ticket_status_id' => $defaultStatus?->id,
                'priority' => $priority,
                'custom_fields' => $validatedFields ?: null,
            ]);

            if ($tags) {
                $this->syncTags->handle($ticket, $tags);
            }

            $this->recordEvent->handle($ticket, TicketEventType::Created, $actor);
            $this->slaHooks->ticketCreated($ticket);
            $this->automationHooks->ticketCreated($ticket);

            return $ticket;
        });
    }
}
