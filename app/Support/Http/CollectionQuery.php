<?php

namespace App\Support\Http;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class CollectionQuery
{
    private int $page;

    private int $perPage;

    private ?string $sort;

    private array $filters;

    private ?string $searchQuery;

    private array $includes;

    private const MAX_PER_PAGE = 100;

    private const DEFAULT_PER_PAGE = 25;

    public function __construct(
        private Request $request,
        private CollectionQuerySpec $spec,
    ) {
        $this->page = max(1, (int) $request->input('page', 1));
        $this->perPage = (int) $request->input('per_page', self::DEFAULT_PER_PAGE);
        $this->sort = $request->input('sort');
        $this->searchQuery = $request->input('filter.q');
        $this->includes = array_filter(explode(',', $request->input('include', '')));
        $this->filters = [];

        $this->validate();
        $this->parseFilters();
    }

    private function validate(): void
    {
        if ($this->perPage < 1 || $this->perPage > self::MAX_PER_PAGE) {
            throw ValidationException::withMessages([
                'per_page' => ['per_page must be between 1 and '.self::MAX_PER_PAGE.'.'],
            ]);
        }

        if ($this->sort && ! in_array($this->sort, $this->spec->allowedSorts) && ! in_array(ltrim($this->sort, '-'), $this->spec->allowedSorts)) {
            throw ValidationException::withMessages([
                'sort' => ['Unknown sort field: '.$this->sort.'.'],
            ]);
        }

        foreach ($this->includes as $include) {
            if (! empty($this->spec->allowedIncludes) && ! in_array($include, $this->spec->allowedIncludes)) {
                throw ValidationException::withMessages([
                    'include' => ['Unknown include: '.$include.'.'],
                ]);
            }
        }
    }

    private function parseFilters(): void
    {
        $filterInput = $this->request->input('filter', []);

        if (is_array($filterInput)) {
            foreach ($filterInput as $field => $conditions) {
                if ($field === 'q') {
                    continue;
                }

                if (! is_array($conditions)) {
                    continue;
                }

                if (! array_key_exists($field, $this->spec->allowedFilters)) {
                    throw ValidationException::withMessages([
                        'filter' => ['Unknown filter field: '.$field.'.'],
                    ]);
                }

                $allowedOps = $this->spec->allowedFilters[$field];

                foreach ($conditions as $op => $value) {
                    if (! in_array($op, $allowedOps)) {
                        throw ValidationException::withMessages([
                            'filter' => ['Unknown operator for '.$field.': '.$op.'.'],
                        ]);
                    }

                    $this->filters[$field][$op] = $value;
                }
            }
        }
    }

    public function applyTo(Builder $query): Builder
    {
        if ($this->sort) {
            $field = ltrim($this->sort, '-');
            $direction = str_starts_with($this->sort, '-') ? 'desc' : 'asc';
            $query = $query->orderBy($field, $direction);
        }

        foreach ($this->filters as $field => $conditions) {
            foreach ($conditions as $op => $value) {
                $query = match ($op) {
                    'eq' => $query->where($field, '=', $value),
                    'neq' => $query->where($field, '!=', $value),
                    'gt' => $query->where($field, '>', $value),
                    'gte' => $query->where($field, '>=', $value),
                    'lt' => $query->where($field, '<', $value),
                    'lte' => $query->where($field, '<=', $value),
                    'in' => $query->whereIn($field, (array) $value),
                    'like' => $query->where($field, 'like', '%'.$value.'%'),
                    default => $query,
                };
            }
        }

        if ($this->searchQuery && ! empty($this->spec->searchableColumns)) {
            $query = $query->where(function ($q) {
                foreach ($this->spec->searchableColumns as $column) {
                    $q = $q->orWhere($column, 'like', '%'.$this->searchQuery.'%');
                }

                return $q;
            });
        }

        return $query;
    }

    public function paginate(Builder $query): LengthAwarePaginator
    {
        return $this->applyTo($query)
            ->paginate($this->perPage, ['*'], 'page', $this->page)
            ->appends($this->request->query());
    }

    public function includes(): array
    {
        return $this->includes;
    }

    public function meta(): array
    {
        return [
            'sort' => $this->sort,
            'filters' => $this->filters,
        ];
    }
}
