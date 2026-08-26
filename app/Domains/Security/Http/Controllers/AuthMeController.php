<?php

namespace App\Domains\Security\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthMeController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->uuid,
                'email' => $user->email,
                'name' => $user->name,
                'permission_keys' => $user->permissionKeys(),
                'primary_branch_id' => $user->primaryBranch()->first()?->id,
                'department_ids' => $user->departments()
                    ->pluck('id')
                    ->toArray(),
            ],
        ]);
    }
}
