<?php

namespace App\Domains\Customers\Services;

use Illuminate\Database\Eloquent\Builder;

class CustomerSearch
{
    public function __construct(private readonly TextNormaliser $normaliser) {}

    public function apply(Builder $query, string $rawTerm): Builder
    {
        $term = $this->normaliser->normaliseName($rawTerm);

        return $query
            ->where('name_normalised', 'like', '%'.$term.'%')
            ->orWhereHas('contacts', fn ($q) => $q->where('value_normalised', 'like', '%'.$term.'%'));
    }
}
