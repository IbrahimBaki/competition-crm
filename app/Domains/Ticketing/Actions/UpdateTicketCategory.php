<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Models\TicketCategory;
use App\Domains\Ticketing\Services\TicketCategoryTree;
use App\Support\I18n\BilingualString;

class UpdateTicketCategory
{
    public function __construct(
        private readonly TicketCategoryTree $tree,
    ) {}

    public function handle(
        TicketCategory $category,
        ?string $code = null,
        ?BilingualString $name = null,
        ?TicketCategory $parent = null,
        ?bool $isActive = null,
    ): TicketCategory {
        $updates = [];

        if ($code !== null) {
            $updates['code'] = $code;
        }

        if ($name !== null) {
            $updates['name'] = $name;
        }

        if ($parent !== null) {
            $this->tree->assertCanNest($parent);
            $updates['parent_id'] = $parent->id;
            $updates['depth'] = $parent->depth + 1;
        }

        if ($isActive !== null) {
            $updates['is_active'] = $isActive;
        }

        if ($updates) {
            $category->update($updates);
        }

        return $category->fresh();
    }
}
