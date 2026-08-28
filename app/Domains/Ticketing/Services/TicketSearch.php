<?php

namespace App\Domains\Ticketing\Services;

use App\Domains\Customers\Services\TextNormaliser;
use Illuminate\Database\Eloquent\Builder;

class TicketSearch
{
    private const MAX_SEARCH_LENGTH = 1024;

    public function __construct(
        private readonly TextNormaliser $normaliser,
    ) {}

    public function apply(Builder $query, string $term): Builder
    {
        $term = substr($term, 0, self::MAX_SEARCH_LENGTH);

        $normalised = $this->normaliser->normaliseName($term);

        return $query->where(function (Builder $q) use ($term, $normalised) {
            $q->where('reference', 'ilike', $term)
                ->orWhere('subject_normalised', 'like', "%{$normalised}%")
                ->orWhere('body_normalised', 'like', "%{$normalised}%");
        });
    }
}
