<?php

namespace App\Domains\Organisation\Http\Controllers;

use App\Domains\Organisation\Actions\AttachUserToBranch;
use App\Domains\Organisation\Actions\AttachUserToDepartment;
use App\Domains\Organisation\Actions\DetachUserFromBranch;
use App\Domains\Organisation\Actions\DetachUserFromDepartment;
use App\Domains\Organisation\Actions\SetPrimaryBranchForUser;
use App\Models\User;
use Illuminate\Routing\Controller;

class UserPlacementController extends Controller
{
    public function attachBranch(User $user, string $branch, AttachUserToBranch $action)
    {
        $action->execute($user, $branch, auth()->user());

        return response()->json(['message' => 'Branch attached'], 200);
    }

    public function detachBranch(User $user, string $branch, DetachUserFromBranch $action)
    {
        $action->execute($user, $branch, auth()->user());

        return response()->json(['message' => 'Branch detached'], 200);
    }

    public function setPrimaryBranch(User $user, string $branch, SetPrimaryBranchForUser $action)
    {
        $action->execute($user, $branch, auth()->user());

        return response()->json(['message' => 'Primary branch set'], 200);
    }

    public function attachDepartment(User $user, string $department, AttachUserToDepartment $action)
    {
        $action->execute($user, $department, auth()->user());

        return response()->json(['message' => 'Department attached'], 200);
    }

    public function detachDepartment(User $user, string $department, DetachUserFromDepartment $action)
    {
        $action->execute($user, $department, auth()->user());

        return response()->json(['message' => 'Department detached'], 200);
    }
}
