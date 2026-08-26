<?php

namespace App\Support\Attachments\Jobs;

use App\Support\Attachments\Attachment;
use App\Support\Attachments\AttachmentStorage;
use App\Support\Attachments\Scanning\MalwareScanner;
use App\Support\Attachments\ScanState;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ScanUploadedFile implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $attachmentId,
    ) {}

    public function handle(MalwareScanner $scanner, AttachmentStorage $storage): void
    {
        $attachment = Attachment::find($this->attachmentId);

        if (! $attachment) {
            return;
        }

        try {
            $path = Storage::disk($attachment->disk)->path($attachment->storage_key);
            $result = $scanner->scan($path);

            $attachment->update([
                'scan_state' => $result->state,
                'scan_reason' => $result->reason,
                'scanned_at' => now(),
            ]);

            if ($result->state === ScanState::Infected) {
                $storage->delete($attachment);
            }
        } catch (\Throwable $e) {
            $attachment->update([
                'scan_state' => ScanState::Failed,
                'scan_reason' => $e->getMessage(),
                'scanned_at' => now(),
            ]);

            throw $e;
        }
    }
}
