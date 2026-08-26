<?php

namespace App\Domains\Sla\Services;

use App\Domains\Sla\Models\SlaPolicy;
use App\Domains\Sla\Models\SlaTarget;
use App\Domains\Sla\Models\SlaTargetType;
use App\Domains\Ticketing\Models\Ticket;

/**
 * Resolves the applicable SLA policy and target for a ticket.
 *
 * Policy resolution: picks the active policy for the ticket's branch,
 * falling back to the default policy if present.
 *
 * Target resolution: deterministic specificity ordering, most specific first:
 * 1. priority + category + tier
 * 2. priority + category
 * 3. priority + tier
 * 4. priority only
 *
 * When no policy or no target matches, returns null.
 */
class SlaPolicyResolver
{
    public function resolveFor(Ticket $ticket): ?SlaPolicy
    {
        $branch = $ticket->department->branch;

        $policy = SlaPolicy::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->first();

        if ($policy) {
            return $policy;
        }

        return SlaPolicy::where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    public function resolveTarget(
        SlaPolicy $policy,
        Ticket $ticket,
        SlaTargetType $type,
    ): ?SlaTarget {
        $priority = $ticket->priority?->value;
        $categoryId = $ticket->ticket_category_id;
        $serviceTier = $ticket->customer?->service_tier?->value;

        if (! $priority) {
            return null;
        }

        // 1. priority + category + tier
        $target = SlaTarget::where('sla_policy_id', $policy->id)
            ->where('target_type', $type->value)
            ->where('priority', $priority)
            ->where('ticket_category_id', $categoryId)
            ->where('service_tier', $serviceTier)
            ->first();

        if ($target) {
            return $target;
        }

        // 2. priority + category
        if ($categoryId) {
            $target = SlaTarget::where('sla_policy_id', $policy->id)
                ->where('target_type', $type->value)
                ->where('priority', $priority)
                ->where('ticket_category_id', $categoryId)
                ->whereNull('service_tier')
                ->first();

            if ($target) {
                return $target;
            }
        }

        // 3. priority + tier
        if ($serviceTier) {
            $target = SlaTarget::where('sla_policy_id', $policy->id)
                ->where('target_type', $type->value)
                ->where('priority', $priority)
                ->whereNull('ticket_category_id')
                ->where('service_tier', $serviceTier)
                ->first();

            if ($target) {
                return $target;
            }
        }

        // 4. priority only
        return SlaTarget::where('sla_policy_id', $policy->id)
            ->where('target_type', $type->value)
            ->where('priority', $priority)
            ->whereNull('ticket_category_id')
            ->whereNull('service_tier')
            ->first();
    }
}
