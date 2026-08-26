<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Http\Requests\UpdateAuthPolicyRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class AuthPolicyController extends Controller
{
    use AuthorizesRequests;

    public function show(Request $request)
    {
        $policy = DB::table('auth_settings')->where('id', 1)->first();

        return response()->json([
            'data' => [
                'require_two_factor' => (bool) $policy->require_two_factor,
            ],
        ]);
    }

    public function update(UpdateAuthPolicyRequest $request)
    {
        DB::table('auth_settings')
            ->where('id', 1)
            ->update([
                'require_two_factor' => $request->input('require_two_factor'),
                'updated_at' => now(),
            ]);

        return response()->json([
            'data' => [
                'require_two_factor' => $request->input('require_two_factor'),
            ],
        ]);
    }
}
