<?php

namespace App\Domains\Ai\Http\Controllers;

use App\Domains\Ai\Actions\ResolveAiSuggestion;
use App\Domains\Ai\Http\Resources\AiSuggestionResource;
use App\Domains\Ai\Models\AiSuggestion;
use App\Domains\Security\Permissions\PermissionKey;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class AiSuggestionController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        Gate::authorize(PermissionKey::AI_SUGGESTIONS_RESOLVE);

        $suggestions = AiSuggestion::paginate();

        return ApiResponse::collection($suggestions);
    }

    public function resolve(AiSuggestion $suggestion, ResolveAiSuggestion $action, string $decision)
    {
        Gate::authorize(PermissionKey::AI_SUGGESTIONS_RESOLVE);

        $action->handle($suggestion, auth()->user(), $decision);

        return ApiResponse::ok(new AiSuggestionResource($suggestion->fresh()));
    }
}
