<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services\Retention;

use App\Domains\Reporting\Models\ReportExport;
use App\Support\Attachments\AttachmentStorage;
use App\Support\Retention\PurgeHandler;
use Carbon\CarbonImmutable;

class ReportExportPurgeHandler implements PurgeHandler
{
    public function __construct(
        private AttachmentStorage $attachmentStorage,
    ) {}

    public function purge(): int
    {
        $expired = ReportExport::where('expires_at', '<', CarbonImmutable::now())
            ->get();

        $count = 0;
        foreach ($expired as $export) {
            // Delete attachment blob if exists
            if ($export->attachment_id) {
                $export->attachment()->delete();
            }

            $export->delete();
            $count++;
        }

        return $count;
    }
}
