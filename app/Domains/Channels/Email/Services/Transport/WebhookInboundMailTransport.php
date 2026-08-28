<?php

namespace App\Domains\Channels\Email\Services\Transport;

use App\Domains\Channels\Email\Models\InboundEmailMessage;
use App\Domains\Channels\Email\Models\InboundState;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebhookInboundMailTransport implements InboundMailTransport
{
    public function store(string $rawMime, string $provider, ?string $providerEventId): InboundEmailMessage
    {
        $uuid = Str::uuid();
        $path = "email/inbound/{$uuid}.eml";

        Storage::disk('local')->put($path, $rawMime, 'private');

        return InboundEmailMessage::create([
            'provider' => $provider,
            'provider_event_id' => $providerEventId,
            'raw_path' => $path,
            'from_address' => '',
            'to_address' => '',
            'classification' => 'unknown',
            'state' => InboundState::Received->value,
            'received_at' => now(),
        ]);
    }
}
