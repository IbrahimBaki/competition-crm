<?php

namespace App\Domains\Customers\Services\Timeline;

use App\Domains\Customers\Models\Customer;

interface TimelineSource
{
    /** Stable key used in the `source` field of each entry. */
    public function key(): string;

    /** @return array<int, TimelineEntry> entries strictly before $before, newest first, at most $limit. */
    public function entriesFor(Customer $customer, ?\DateTimeInterface $before, int $limit): array;
}
