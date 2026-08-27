<?php

namespace App\Domains\Channels\Messaging\Services\Transport;

use App\Domains\Ticketing\Models\TicketMessage;

interface ProviderMessageTransport
{
    public function send(TicketMessage $message): ProviderSendResult;
}
