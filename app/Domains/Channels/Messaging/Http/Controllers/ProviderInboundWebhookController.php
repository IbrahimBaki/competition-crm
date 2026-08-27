<?php

namespace App\Domains\Channels\Messaging\Http\Controllers;

use App\Domains\Channels\Messaging\Jobs\ProcessProviderInboundMessageJob;
use App\Domains\Ticketing\Models\MessageChannel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ProviderInboundWebhookController extends Controller
{
    public function handleWhatsapp(Request $request): Response
    {
        return $this->handleInbound($request, MessageChannel::Whatsapp);
    }

    public function handleSms(Request $request): Response
    {
        return $this->handleInbound($request, MessageChannel::Sms);
    }

    private function handleInbound(Request $request, MessageChannel $channel): Response
    {
        $secret = config("channels.{$channel->value}.webhook_secret");

        if ($secret && ! $this->verifySignature($request, $secret)) {
            return response('Unauthorized', 401);
        }

        ProcessProviderInboundMessageJob::dispatch(
            channel: $channel,
            payload: $request->all(),
            receivedAt: now(),
        );

        return response('Accepted', 202);
    }

    private function verifySignature(Request $request, string $secret): bool
    {
        $signature = $request->header('X-Provider-Signature');

        if (! $signature) {
            return false;
        }

        $body = $request->getContent();
        $hash = hash_hmac('sha256', $body, $secret);

        return hash_equals($hash, $signature);
    }
}
