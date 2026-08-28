<?php

namespace App\Domains\Channels\Messaging\Http\Controllers;

use App\Domains\Channels\Messaging\Http\Resources\ProviderMessageTemplateResource;
use App\Domains\Channels\Messaging\Models\ProviderMessageTemplate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller;

class ProviderMessageTemplateController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResource
    {
        $this->authorize('viewAny', ProviderMessageTemplate::class);

        $perPage = min((int) $request->query('per_page', 25), 100);

        $templates = ProviderMessageTemplate::query()
            ->where('is_active', true)
            ->paginate($perPage);

        return ProviderMessageTemplateResource::collection($templates)
            ->response()
            ->setStatusCode(200);
    }
}
