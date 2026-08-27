<?php

namespace App\Domains\Portal\Http\Controllers;

use App\Domains\Portal\Models\PortalAccount;
use App\Domains\Portal\Services\Visibility\PortalAttachmentGuard;
use App\Support\Attachments\Attachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PortalAttachmentController
{
    public function __construct(private PortalAttachmentGuard $guard) {}

    public function show(Request $request, string $attachment): JsonResponse|BinaryFileResponse|Response
    {
        /** @var PortalAccount|null $account */
        $account = $request->user('portal');
        $attachment = Attachment::where('uuid', $attachment)->firstOrFail();

        if (! $account || ! $this->guard->canDownload($account, $attachment)) {
            return response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Attachment not found',
                ],
            ], 404);
        }

        $path = storage_path('app/attachments/'.$attachment->getAttribute('path'));

        return response()->download($path, $attachment->getAttribute('original_name'));
    }
}
