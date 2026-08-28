<?php

namespace App\Domains\Channels\WebForm\Services\Retention;

use App\Domains\Channels\WebForm\Models\WebFormSubmission;
use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;

class WebFormSubmissionPurgeHandler implements PurgeHandler
{
    public function dataClass(): string
    {
        return WebFormSubmission::class;
    }

    public function purge(RetentionPolicy $policy): int
    {
        $retentionDays = config('channels.web_form.submission_retention_days', 90);
        $cutoffDate = now()->subDays($retentionDays);

        $count = WebFormSubmission::where('created_at', '<', $cutoffDate)
            ->update([
                'payload' => null,
                'submitter_ip_hash' => null,
                'user_agent' => null,
            ]);

        return $count;
    }
}
