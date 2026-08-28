<?php

namespace App\Domains\Ticketing\Services;

use App\Domains\Ticketing\Exceptions\TicketCategoryDepthExceededException;
use App\Domains\Ticketing\Models\TicketCategory;
use App\Domains\Ticketing\Models\TicketCategoryField;
use Illuminate\Support\Collection;

class TicketCategoryTree
{
    public function maxDepth(): int
    {
        return 3;
    }

    public function assertCanNest(?TicketCategory $parent): void
    {
        if ($parent !== null && $parent->depth >= $this->maxDepth()) {
            throw new TicketCategoryDepthExceededException;
        }
    }

    /**
     * Resolve field definitions for a category, merging ancestor fields with child overriding.
     *
     * @return Collection<string, TicketCategoryField>
     */
    public function resolveFieldDefinitions(TicketCategory $category): Collection
    {
        $fields = collect();

        $ancestors = array_reverse($category->ancestors());
        $ancestors[] = $category;

        foreach ($ancestors as $ancestor) {
            foreach ($ancestor->fields as $field) {
                $fields[$field->key] = $field;
            }
        }

        return $fields;
    }
}
