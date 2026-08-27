<?php

namespace App\Domains\Portal\Services\Guest;

use App\Domains\Channels\WebForm\Models\WebFormSubmission;
use App\Domains\Portal\Models\PortalGuestGrant;
use Illuminate\Support\Str;

class IssueGuestGrant
{
    public function forSubmission(WebFormSubmission $submission): string
    {
        $plainToken = Str::random(64);
        $tokenHash = hash('sha256', $plainToken);

        PortalGuestGrant::create([
            'uuid' => (string) Str::uuid(),
            'token_hash' => $tokenHash,
            'web_form_submission_id' => $submission->id,
            'expires_at' => now()->addDays(config('portal.guest_grant_days', 30)),
        ]);

        return $plainToken;
    }
}
