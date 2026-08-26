<?php

namespace App\Domains\Ticketing\Http\Resources;

use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Domains\Ticketing\Services\Lifecycle\TicketTransitionMap;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $availableTransitions = [];

        if (! $this->isMerged() && $this->status) {
            $transitionMap = app(TicketTransitionMap::class);
            $currentType = $this->lifecycleType();

            foreach ($transitionMap->allowedFrom($currentType) as $targetType) {
                $targetStatus = TicketStatusDefinition::query()
                    ->where('lifecycle_type', $targetType->value)
                    ->where('is_active', true)
                    ->first();

                if ($targetStatus && $request->user()?->can('changeStatus', $this)) {
                    $availableTransitions[] = [
                        'uuid' => $targetStatus->uuid,
                        'key' => $targetStatus->key,
                        'name' => $targetStatus->name,
                        'lifecycle_type' => $targetStatus->lifecycle_type->value,
                        'requires_reason' => $transitionMap->requiresReason($currentType, $targetType),
                    ];
                }
            }
        }

        return [
            'id' => $this->uuid,
            'reference' => $this->reference,
            'customer_id' => $this->customer?->uuid,
            'department_id' => $this->department_id,
            'category_id' => $this->category?->uuid,
            'assignee_id' => $this->assignee?->uuid,
            'subject' => $this->subject,
            'body' => $this->body,
            'status' => $this->status ? [
                'uuid' => $this->status->uuid,
                'key' => $this->status->key,
                'name' => $this->status->name,
                'lifecycle_type' => $this->status->lifecycle_type->value,
                'stops_sla_clock' => $this->status->stopsSlaClock(),
            ] : null,
            'priority' => $this->priority?->value,
            'custom_fields' => $this->custom_fields,
            'available_transitions' => $availableTransitions,
            'merged_into_id' => $this->mergedInto?->uuid,
            'parent_ticket_id' => $this->parent?->uuid,
            'reopen_deadline_at' => $this->reopen_deadline_at?->toIso8601String(),
            'reopened_count' => $this->reopened_count,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
