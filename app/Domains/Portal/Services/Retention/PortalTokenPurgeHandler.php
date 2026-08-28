<?php

namespace App\Domains\Portal\Services\Retention;

use App\Domains\Portal\Models\PortalGuestGrant;
use App\Domains\Portal\Models\PortalVerificationToken;
use App\Domains\Portal\Models\TicketFeedbackInvitation;
use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;

class PortalTokenPurgeHandler implements PurgeHandler
{
    public function dataClass(): string
    {
        return 'portal.tokens';
    }

    public function purge(RetentionPolicy $policy): int
    {
        $days = (int) config('retention.portal_tokens.days_after_expiry', 30);
        $cutoff = now()->subDays($days);

        $count = 0;

        $count += PortalVerificationToken::whereNotNull('consumed_at')
            ->where('consumed_at', '<', $cutoff)
            ->delete();

        $count += PortalVerificationToken::whereNull('consumed_at')
            ->where('expires_at', '<', $cutoff)
            ->delete();

        $count += PortalGuestGrant::where('expires_at', '<', $cutoff)->delete();

        $count += TicketFeedbackInvitation::whereNotNull('consumed_at')
            ->where('consumed_at', '<', $cutoff)
            ->delete();

        $count += TicketFeedbackInvitation::whereNull('consumed_at')
            ->where('expires_at', '<', $cutoff)
            ->delete();

        return $count;
    }
}
