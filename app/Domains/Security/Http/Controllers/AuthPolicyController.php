<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Http\Requests\UpdateAuthPolicyRequest;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
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

    public function update(UpdateAuthPolicyRequest $request, AuditLogger $auditLogger)
    {
        $before = DB::table('auth_settings')->where('id', 1)->first()?->toArray() ?? [];

        DB::table('auth_settings')
            ->where('id', 1)
            ->update([
                'require_two_factor' => $request->input('require_two_factor'),
                'updated_at' => now(),
            ]);

        $after = DB::table('auth_settings')->where('id', 1)->first()?->toArray() ?? [];

        // Create a stub model for audit trail
        $stub = new class extends Model
        {
            protected $table = 'auth_settings';
        };
        $stub->id = 1;

        $auditLogger->record($request->user(), 'security.auth_policy.updated', $stub, $before, $after);

        return response()->json([
            'data' => [
                'require_two_factor' => $request->input('require_two_factor'),
            ],
        ]);
    }
}
