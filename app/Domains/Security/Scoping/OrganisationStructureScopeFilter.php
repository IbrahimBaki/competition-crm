<?php

namespace App\Domains\Security\Scoping;

use App\Domains\Organisation\Models\Department;
use App\Domains\Security\Permissions\Scope;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class OrganisationStructureScopeFilter implements ScopeFilter
{
    public function apply(Builder $query, User $user, Scope $scope): Builder
    {
        return match ($scope) {
            Scope::Any => $query,
            Scope::Department => $query->whereIn('department_id', $user->departments()->pluck('id')),
            Scope::Team => $query->whereIn('team_id', $user->teams()->pluck('id') ?? []),
            Scope::Own => $query->where('user_uuid', $user->uuid),
        };
    }

    /**
     * Structural reachability for a single record.
     *
     * Answers only "is this department within the user's part of the org?" —
     * the caller still applies the own/team/department/any permission scope
     * afterwards (see TicketPolicy). A record with no department carries no
     * structural restriction.
     */
    public function allows(User $user, ?Department $department): bool
    {
        if ($department === null) {
            return true;
        }

        if ($user->departments()->whereKey($department->getKey())->exists()) {
            return true;
        }

        // A user attached to the branch can reach that branch's departments.
        return $department->branch_id !== null
            && $user->branches()->whereKey($department->branch_id)->exists();
    }
}
