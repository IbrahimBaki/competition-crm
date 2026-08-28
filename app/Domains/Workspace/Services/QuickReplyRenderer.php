<?php

namespace App\Domains\Workspace\Services;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Workspace\Models\QuickReply;
use App\Models\User;
use App\Support\I18n\LocaleResolver;

readonly class QuickReplyRenderer
{
    public function __construct(
        private LocaleResolver $localeResolver,
    ) {}

    public function render(QuickReply $reply, ?Ticket $ticket = null, ?User $user = null, ?string $locale = null): string
    {
        $locale ??= $this->localeResolver->resolveLocale();

        $body = match ($locale) {
            'ar' => $reply->body['ar'] ?? '',
            default => $reply->body['en'] ?? '',
        };

        $customerName = $ticket?->customer?->name ?? '';
        $ticketReference = $ticket?->reference ?? '';
        $agentName = $user?->name ?? '';

        $body = str_replace('{{customer_name}}', $customerName, $body);
        $body = str_replace('{{ticket_reference}}', $ticketReference, $body);
        $body = str_replace('{{agent_name}}', $agentName, $body);

        return $body;
    }
}
