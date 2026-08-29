<?php

namespace App\Domains\Integrations\Http\Controllers;

use App\Domains\Integrations\Actions\IssueApiToken;
use App\Domains\Integrations\Actions\RevokeApiToken;
use App\Domains\Integrations\Http\Requests\StoreApiTokenRequest;
use App\Domains\Integrations\Http\Resources\ApiTokenResource;
use App\Domains\Integrations\Http\Resources\IssuedApiTokenResource;
use App\Domains\Integrations\Models\ApiToken;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

final class ApiTokenController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly IssueApiToken $issueApiToken,
        private readonly RevokeApiToken $revokeApiToken,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', ApiToken::class);

        $tokens = ApiToken::orderByDesc('created_at')->paginate();

        return ApiResponse::paginated(
            ApiTokenResource::collection($tokens),
            $tokens,
        );
    }

    public function store(StoreApiTokenRequest $request)
    {
        $dto = $this->issueApiToken->execute(
            name: $request->validated('name'),
            scopeStrings: $request->validated('scopes'),
            actor: $request->user(),
        );

        return ApiResponse::success(
            new IssuedApiTokenResource($dto),
            status: 201,
        );
    }

    public function destroy(ApiToken $token)
    {
        $this->authorize('delete', $token);

        $this->revokeApiToken->execute($token, auth()->user());

        return ApiResponse::success(null, status: 204);
    }
}
