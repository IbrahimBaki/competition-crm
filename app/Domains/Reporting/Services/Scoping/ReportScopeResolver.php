<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Scoping;

use App\Domains\Reporting\Services\Filters\ReportFilter;
use App\Domains\Security\Permissions\Scope;
use App\Domains\Security\Scoping\OrganisationStructureScopeFilter;
use App\Domains\Security\Scoping\ResolveEffectiveScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

readonly class ReportScopeResolver
{
    public function __construct(
        private ResolveEffectiveScope $resolveScope,
        private OrganisationStructureScopeFilter $organisationFilter,
    ) {}

    /**
     * Narrow the report filter to the caller's effective scope.
     * Returns the narrowed filter and a query constraint closure.
     *
     * @return array{filter: ReportFilter, scope: \Closure}
     */
    public function resolve(User $user, string $reportKey, ReportFilter $filter): array
    {
        $scope = $this->resolveScope->resolve($user, 'reports', 'view');

        // Build the narrowed filter based on scope
        $narrowedFilter = $this->narrowFilter($user, $scope, $filter);

        // Return a closure that applies scope to a query
        $scopeClosure = fn (Builder $query) => $this->organisationFilter->apply($query, $user, $scope);

        return [
            'filter' => $narrowedFilter,
            'scope' => $scopeClosure,
        ];
    }

    private function narrowFilter(User $user, ?Scope $scope, ReportFilter $filter): ReportFilter
    {
        return match ($scope) {
            Scope::Own => $this->narrowToOwn($user, $filter),
            Scope::Team => $this->narrowToTeam($user, $filter),
            Scope::Department => $this->narrowToDepartment($user, $filter),
            Scope::Any, null => $filter,
        };
    }

    private function narrowToOwn(User $user, ReportFilter $filter): ReportFilter
    {
        return new ReportFilter(
            from: $filter->from,
            to: $filter->to,
            timezone: $filter->timezone,
            branchId: $filter->branchId,
            departmentId: $filter->departmentId,
            teamId: $filter->teamId,
            agentId: $user->id,
            categoryId: $filter->categoryId,
            priority: $filter->priority,
            channel: $filter->channel,
            tagId: $filter->tagId,
        );
    }

    private function narrowToTeam(User $user, ReportFilter $filter): ReportFilter
    {
        // Team scoping is handled by the organisation structure filter on the query
        return $filter;
    }

    private function narrowToDepartment(User $user, ReportFilter $filter): ReportFilter
    {
        // Department scoping is handled by the organisation structure filter on the query
        return $filter;
    }
}
