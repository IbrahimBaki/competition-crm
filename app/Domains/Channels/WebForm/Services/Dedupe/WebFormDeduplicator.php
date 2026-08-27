<?php

namespace App\Domains\Channels\WebForm\Services\Dedupe;

use App\Domains\Channels\WebForm\Models\WebForm;
use App\Domains\Channels\WebForm\Models\WebFormSubmission;
use App\Domains\Channels\WebForm\Models\WebFormSubmissionState;

final class WebFormDeduplicator
{
    public function findRecent(WebForm $form, string $fingerprint): ?WebFormSubmission
    {
        $windowMinutes = config('channels.web_form.dedupe.window_minutes', 10);
        $windowStart = now()->subMinutes($windowMinutes);

        return WebFormSubmission::query()
            ->where('web_form_id', $form->id)
            ->where('fingerprint', $fingerprint)
            ->where('state', WebFormSubmissionState::Accepted->value)
            ->where('created_at', '>=', $windowStart)
            ->orderByDesc('created_at')
            ->first();
    }
}
