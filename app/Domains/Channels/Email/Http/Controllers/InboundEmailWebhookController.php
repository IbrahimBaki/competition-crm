<?php

namespace App\Domains\Channels\Email\Http\Controllers;

use App\Domains\Channels\Email\Jobs\ProcessInboundEmailJob;
use App\Domains\Channels\Email\Services\Transport\InboundMailTransport;
use App\Support\Http\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InboundEmailWebhookController
{
    public function __construct(private readonly InboundMailTransport $transport) {}

    public function store(Request $request): Response
    {
        $provider = $request->input('provider', 'webhook');
        $providerEventId = $request->input('event_id');
        $rawMime = $request->input('message');

        $record = $this->transport->store($rawMime, $provider, $providerEventId);

        ProcessInboundEmailJob::dispatch($record->id)->onQueue('email');

        return response()->json(
            ApiResponse::success(['id' => $record->uuid]),
            202,
        );
    }
}
