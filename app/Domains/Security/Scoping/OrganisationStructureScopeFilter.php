<?php

namespace App\Domains\Security\Scoping;

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
}
