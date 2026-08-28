<?php

namespace App\Domains\Notifications\Services\Channels;

use App\Domains\Notifications\Enums\NotificationChannel;

final class NotificationChannelRegistry
{
    /** @var array<string, NotificationChannelTransport> */
    private array $channels = [];

    public function register(NotificationChannel $channel, NotificationChannelTransport $transport): self
    {
        $this->channels[$channel->value] = $transport;

        return $this;
    }

    public function get(string $channelKey): ?NotificationChannelTransport
    {
        return $this->channels[$channelKey] ?? null;
    }
}
