<?php

namespace App\Domains\Ticketing\Http\Resources;

use App\Domains\Sla\Http\Resources\TicketSlaResource;
use App\Domains\Sla\Models\SlaTargetType;
use App\Domains\Sla\Services\SlaClockService;
use App\Domains\Ticketing\Models\TicketStatusDefinition;
use App\Domains\Ticketing\Services\Lifecycle\TicketTransitionMap;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $availableTransitions = [];
        $statusDefinition = $this->statusDefinition;

        if (! $this->isMerged()) {
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
            'status' => $statusDefinition ? [
                'uuid' => $statusDefinition->uuid,
                'key' => $statusDefinition->key,
                'name' => $statusDefinition->name,
                'lifecycle_type' => $statusDefinition->lifecycle_type->value,
                'stops_sla_clock' => $statusDefinition->stopsSlaClock(),
            ] : [
                'uuid' => null,
                'key' => $this->lifecycleType()->value,
                'name' => ['en' => ucfirst($this->lifecycleType()->value), 'ar' => ucfirst($this->lifecycleType()->value)],
                'lifecycle_type' => $this->lifecycleType()->value,
                'stops_sla_clock' => $this->lifecycleType()->stopsSlaClock(),
            ],
            'priority' => $this->priority?->value,
            'custom_fields' => $this->custom_fields,
            'available_transitions' => $availableTransitions,
            'merged_into_id' => $this->mergedInto?->uuid,
            'parent_ticket_id' => $this->parent?->uuid,
            'version' => $this->version,
            'assigned_at' => $this->assigned_at?->toIso8601String(),
            'reopen_deadline_at' => $this->reopen_deadline_at?->toIso8601String(),
            'reopened_count' => $this->reopened_count,
            'is_watched' => $request->user() ? $this->watchers()->where('user_id', $request->user()->id)->exists() : false,
            'watcher_count' => $this->whenCounted('watchers'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'sla' => $this->computeSlaBlock(),
        ];
    }

    private function computeSlaBlock(): ?array
    {
        $clockService = app(SlaClockService::class);
        $now = CarbonImmutable::now('UTC');

        if (! $this->relationLoaded('slaClocks')) {
            return null;
        }

        $clocks = $this->slaClocks;

        $result = [];

        foreach ([SlaTargetType::FirstResponse, SlaTargetType::Resolution] as $type) {
            $clock = $clocks->first(
                fn ($candidate) => ($candidate->target_type instanceof SlaTargetType
                    ? $candidate->target_type
                    : SlaTargetType::from($candidate->target_type)) === $type
            );

            if ($clock) {
                $position = $clockService->position($clock, $now);
                $result[$type->value] = new TicketSlaResource($position);
            } else {
                $result[$type->value] = null;
            }
        }

        return [
            'first_response' => $result[SlaTargetType::FirstResponse->value],
            'resolution' => $result[SlaTargetType::Resolution->value],
        ];
    }
}
