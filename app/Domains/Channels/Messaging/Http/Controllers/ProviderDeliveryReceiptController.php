<?php

namespace App\Domains\Channels\Messaging\Http\Controllers;

use App\Domains\Channels\Messaging\Jobs\ApplyProviderDeliveryReceiptJob;
use App\Domains\Ticketing\Models\MessageChannel;
use App\Domains\Ticketing\Models\MessageDeliveryState;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ProviderDeliveryReceiptController extends Controller
{
    public function handleWhatsapp(Request $request): Response
    {
        return $this->handleReceipt($request, MessageChannel::Whatsapp);
    }

    public function handleSms(Request $request): Response
    {
        return $this->handleReceipt($request, MessageChannel::Sms);
    }

    private function handleReceipt(Request $request, MessageChannel $channel): Response
    {
        $secret = config("channels.{$channel->value}.webhook_secret");

        if ($secret && ! $this->verifySignature($request, $secret)) {
            return response('Unauthorized', 401);
        }

        $providerMessageId = $request->input('id') ?? $request->input('message_id');
        $status = $this->mapStatus($request->input('status'));

        if (! $providerMessageId || ! $status) {
            return response('Accepted', 202);
        }

        ApplyProviderDeliveryReceiptJob::dispatch(
            channel: $channel->value,
            providerMessageId: $providerMessageId,
            status: $status,
            failureReason: $request->input('failure_reason'),
            failureDetail: $request->input('failure_detail'),
        );

        return response('Accepted', 202);
    }

    private function mapStatus(string $providerStatus): ?MessageDeliveryState
    {
        return match (strtolower($providerStatus)) {
            'sent', 'queued' => MessageDeliveryState::Sent,
            'delivered' => MessageDeliveryState::Delivered,
            'read' => MessageDeliveryState::Read,
            'failed' => MessageDeliveryState::Failed,
            default => null,
        };
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
