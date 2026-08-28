<?php

namespace App\Domains\Sla\Http\Policies;

use App\Domains\Sla\Models\SlaPolicy;
use App\Models\User;

class SlaPolicyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sla.policies.view');
    }

    public function view(User $user, SlaPolicy $policy): bool
    {
        return $user->can('sla.policies.view');
    }

    public function create(User $user): bool
    {
        return $user->can('sla.policies.manage');
    }

    public function update(User $user, SlaPolicy $policy): bool
    {
        return $user->can('sla.policies.manage');
    }

    public function delete(User $user, SlaPolicy $policy): bool
    {
        return $user->can('sla.policies.manage');
    }
}
