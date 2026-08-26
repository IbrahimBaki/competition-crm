<?php

namespace App\Support\Http;

final class CollectionQuerySpec
{
    public function __construct(
        public array $allowedSorts = [],
        public array $allowedFilters = [],
        public array $allowedIncludes = [],
        public array $searchableColumns = [],
    ) {}

    public static function create(): self
    {
        return new self;
    }

    public function withSorts(array $sorts): self
    {
        $this->allowedSorts = $sorts;

        return $this;
    }

    public function withFilters(array $filters): self
    {
        $this->allowedFilters = $filters;

        return $this;
    }

    public function withIncludes(array $includes): self
    {
        $this->allowedIncludes = $includes;

        return $this;
    }

    public function withSearchableColumns(array $columns): self
    {
        $this->searchableColumns = $columns;

        return $this;
    }
}
