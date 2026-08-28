<?php

namespace App\Domains\Customers\Services\Timeline\Sources;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Services\Timeline\TimelineEntry;
use App\Domains\Customers\Services\Timeline\TimelineSource;
use DateTimeImmutable;

class NoteTimelineSource implements TimelineSource
{
    public function key(): string
    {
        return 'note';
    }

    public function entriesFor(Customer $customer, ?\DateTimeInterface $before, int $limit): array
    {
        $query = $customer->notes();

        if ($before) {
            $query->where('created_at', '<', $before);
        }

        $notes = $query
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit + 1)
            ->get();

        return $notes->map(
            fn ($note) => new TimelineEntry(
                id: $note->uuid,
                source: $this->key(),
                type: 'note',
                occurredAt: DateTimeImmutable::createFromInterface($note->created_at),
                actorUuid: $note->author?->uuid,
                payload: ['body' => $note->body],
            )
        )->all();
    }
}
