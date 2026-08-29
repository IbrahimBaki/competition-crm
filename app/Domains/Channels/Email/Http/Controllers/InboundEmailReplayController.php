<?php

namespace App\Domains\Channels\Email\Http\Controllers;

use App\Domains\Channels\Email\Http\Resources\InboundEmailMessageResource;
use App\Domains\Channels\Email\Jobs\ProcessInboundEmailJob;
use App\Domains\Channels\Email\Models\InboundEmailMessage;
use App\Domains\Channels\Email\Models\InboundState;
use App\Domains\Security\Permissions\PermissionKey;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class InboundEmailReplayController
{
    public function index(Request $request): ApiResponse
    {
        Gate::authorize(PermissionKey::CHANNELS_EMAIL_REPLAY_LIST);

        $spec = CollectionQuerySpec::create()
            ->withSorts(['received_at', 'state', 'attempts'])
            ->withFilters(['state' => ['eq', 'neq']])
            ->withSearchableColumns(['from_address', 'subject', 'message_id']);
        $collection = (new CollectionQuery($request, $spec))
            ->paginate(InboundEmailMessage::query()->with('ticket'));

        return ApiResponse::paginated(
            InboundEmailMessageResource::collection(collect($collection->items())),
            $collection,
        );
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
