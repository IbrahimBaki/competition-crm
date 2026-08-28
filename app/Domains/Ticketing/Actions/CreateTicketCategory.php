<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Models\TicketCategory;
use App\Domains\Ticketing\Services\TicketCategoryTree;
use App\Support\I18n\BilingualString;

class CreateTicketCategory
{
    public function __construct(
        private readonly TicketCategoryTree $tree,
    ) {}

    public function handle(
        string $code,
        BilingualString $name,
        ?TicketCategory $parent = null,
        bool $isActive = true,
    ): TicketCategory {
        $this->tree->assertCanNest($parent);

        $depth = $parent ? $parent->depth + 1 : 1;

        return TicketCategory::create([
            'code' => $code,
            'name' => $name,
            'parent_id' => $parent?->id,
            'depth' => $depth,
            'is_active' => $isActive,
        ]);
    }
}
