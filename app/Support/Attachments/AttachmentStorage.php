<?php

namespace App\Support\Attachments;

use App\Models\User;
use App\Support\Attachments\Jobs\ScanUploadedFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AttachmentStorage
{
    public function store(UploadedFile $file, User $uploader): Attachment
    {
        $storageKey = date('Y/m').'/'.Str::uuid()->toString();
        $disk = config('security.uploads.disk');

        $mimeType = $file->getMimeType();
        $sizeBytes = $file->getSize();

        Storage::disk($disk)->putFileAs(
            dirname($storageKey),
            $file,
            basename($storageKey),
        );

        $attachment = Attachment::create([
            'uuid' => Str::uuid()->toString(),
            'disk' => $disk,
            'storage_key' => $storageKey,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'size_bytes' => $sizeBytes,
            'scan_state' => ScanState::Pending,
            'uploaded_by' => $uploader->id,
        ]);

        ScanUploadedFile::dispatch($attachment->id);

        return $attachment;
    }

    public function stream(Attachment $attachment): StreamedResponse
    {
        $disk = $attachment->disk;
        $storageKey = $attachment->storage_key;

        return Storage::disk($disk)->download($storageKey, $attachment->original_name);
    }

    public function delete(Attachment $attachment): void
    {
        Storage::disk($attachment->disk)->delete($attachment->storage_key);
    }
}
