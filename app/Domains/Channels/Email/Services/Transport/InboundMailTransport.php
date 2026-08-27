<?php

namespace App\Domains\Channels\Email\Services\Transport;

use App\Domains\Channels\Email\Models\InboundEmailMessage;

interface InboundMailTransport
{
    public function store(string $rawMime, string $provider, ?string $providerEventId): InboundEmailMessage;
}
