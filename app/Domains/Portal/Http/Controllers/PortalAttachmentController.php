<?php

namespace App\Domains\Portal\Http\Controllers;

use App\Domains\Portal\Services\Visibility\PortalAttachmentGuard;
use App\Support\Attachments\Attachment;
use Illuminate\Http\Request;

class PortalAttachmentController
{
    public function __construct(private PortalAttachmentGuard $guard) {}

    public function show(Request $request, string $attachment)
    {
        $account = $request->user('portal');
        $attachment = Attachment::where('uuid', $attachment)->firstOrFail();

        if (! $this->guard->canDownload($account, $attachment)) {
            return response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Attachment not found',
                ],
            ], 404);
        }

        return response()->download(storage_path('app/attachments/'.$attachment->path), $attachment->original_name);
    }
}
