<?php

namespace App\Domains\Security\Scoping;

use App\Domains\Organisation\Models\Department;
use App\Domains\Security\Permissions\Scope;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

interface ScopeFilter
{
    public function apply(Builder $query, User $user, Scope $scope): Builder;

    /**
     * Structural reachability check for a single record's department.
     */
    public function allows(User $user, ?Department $department): bool;
}
