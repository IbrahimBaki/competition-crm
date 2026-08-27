<?php

namespace App\Domains\Channels\Email\Http\Controllers;

use App\Domains\Channels\Email\Http\Resources\InboundEmailMessageResource;
use App\Domains\Channels\Email\Jobs\ProcessInboundEmailJob;
use App\Domains\Channels\Email\Models\InboundEmailMessage;
use App\Domains\Channels\Email\Models\InboundState;
use App\Domains\Security\Permissions\PermissionKey;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class InboundEmailReplayController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize(PermissionKey::CHANNELS_EMAIL_REPLAY_LIST);

        $query = InboundEmailMessage::query();

        $state = $request->query('state');
        if ($state) {
            $query->where('state', $state);
        }

        $collection = CollectionQuery::paginate($query, $request);

        return InboundEmailMessageResource::collection($collection);
    }

    public function replay(InboundEmailMessage $record): Response
    {
        Gate::authorize(PermissionKey::CHANNELS_EMAIL_REPLAY_ACTION);

        $record->update([
            'state' => InboundState::Received->value,
            'attempts' => 0,
            'last_error' => null,
        ]);

        ProcessInboundEmailJob::dispatch($record->id)->onQueue('email');

        return response()->json(
            ApiResponse::success(['id' => $record->uuid]),
            202,
        );
    }
}
