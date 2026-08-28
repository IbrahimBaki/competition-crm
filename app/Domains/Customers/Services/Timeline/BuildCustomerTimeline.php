<?php

namespace App\Domains\Customers\Services\Timeline;

use App\Domains\Customers\Models\Customer;

class BuildCustomerTimeline
{
    public const MAX_LIMIT = 100;

    public function __construct(private readonly TimelineRegistry $registry) {}

    public function execute(
        Customer $customer,
        ?\DateTimeInterface $before = null,
        int $limit = 25,
    ): array {
        $limit = min($limit, self::MAX_LIMIT);

        $allEntries = [];

        foreach ($this->registry->sources() as $source) {
            $entries = $source->entriesFor($customer, $before, $limit + 1);
            $allEntries = array_merge($allEntries, $entries);
        }

        // Sort by occurredAt descending, with id as tiebreaker for determinism
        usort($allEntries, function (TimelineEntry $a, TimelineEntry $b) {
            $dateCompare = $b->occurredAt->getTimestamp() <=> $a->occurredAt->getTimestamp();
            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            return $b->id <=> $a->id;
        });

        $nextBefore = null;
        if (count($allEntries) > $limit) {
            array_splice($allEntries, $limit);
            if (count($allEntries) === $limit) {
                $lastEntry = $allEntries[$limit - 1];
                $nextBefore = $lastEntry->occurredAt->format(\DateTimeInterface::ATOM);
            }
        }

        return [
            'entries' => array_splice($allEntries, 0, $limit),
            'next_before' => $nextBefore,
        ];
    }
}
