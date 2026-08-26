<?php

namespace App\Domains\Security\Scoping;

use App\Domains\Security\Permissions\Scope;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

interface ScopeFilter
{
    public function apply(Builder $query, User $user, Scope $scope): Builder;
}
