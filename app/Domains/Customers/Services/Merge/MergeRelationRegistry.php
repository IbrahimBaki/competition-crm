<?php

namespace App\Domains\Customers\Services\Merge;

/**
 * Registry for merge relations.
 *
 * Modules can register their own MergeRelation implementations without
 * modifying the merge action. This pattern matches TimelineRegistry and
 * allows later modules (tickets, messages) to register their relations
 * when they land, keeping the core merge logic module-agnostic.
 */
final class MergeRelationRegistry
{
    /** @var array<int, MergeRelation> */
    private array $relations = [];

    public function register(MergeRelation $relation): void
    {
        $this->relations[] = $relation;
    }

    /** @return array<int, MergeRelation> */
    public function all(): array
    {
        return $this->relations;
    }
}
