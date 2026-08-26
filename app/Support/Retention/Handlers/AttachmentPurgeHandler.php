<?php

namespace App\Support\Retention\Handlers;

use App\Support\Attachments\Attachment;
use App\Support\Attachments\AttachmentStorage;
use App\Support\Retention\PurgeHandler;
use App\Support\Retention\RetentionPolicy;

class AttachmentPurgeHandler implements PurgeHandler
{
    public function __construct(private AttachmentStorage $storage) {}

    public function dataClass(): string
    {
        return 'attachments';
    }

    public function purge(RetentionPolicy $policy): int
    {
        if ($policy->days === null) {
            return 0;
        }

        $batchSize = config('retention.batch_size', 500);
        $totalDeleted = 0;

        while (true) {
            $attachments = Attachment::where('created_at', '<', $policy->cutoff)
                ->limit($batchSize)
                ->get(['uuid', 'disk', 'storage_key']);

            if ($attachments->isEmpty()) {
                break;
            }

            foreach ($attachments as $attachment) {
                try {
                    $this->storage->delete($attachment->disk, $attachment->storage_key);
                } catch (\Exception $e) {
                    \Log::warning("Failed to delete attachment blob: {$attachment->uuid}", [
                        'exception' => $e->getMessage(),
                    ]);
                }

                Attachment::where('uuid', $attachment->uuid)->delete();
                $totalDeleted++;
            }
        }

        return $totalDeleted;
    }
}
