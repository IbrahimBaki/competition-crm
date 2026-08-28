<?php

namespace App\Domains\Customers\Services\Timeline\Sources;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Services\Timeline\TimelineEntry;
use App\Domains\Customers\Services\Timeline\TimelineSource;
use DateTimeImmutable;

class CustomerEventSource implements TimelineSource
{
    public function key(): string
    {
        return 'customer_event';
    }

    public function entriesFor(Customer $customer, ?\DateTimeInterface $before, int $limit): array
    {
        $query = $customer->events();

        if ($before) {
            $query->where('occurred_at', '<', $before);
        }

        $events = $query
            ->orderBy('occurred_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit + 1)
            ->get();

        return $events->map(
            fn ($event) => new TimelineEntry(
                id: $event->uuid,
                source: $this->key(),
                type: $event->type->value,
                occurredAt: DateTimeImmutable::createFromInterface($event->occurred_at),
                actorUuid: $event->actor?->uuid,
                payload: $event->payload ?? [],
            )
        )->all();
    }
}
