<?php

namespace App\Domains\Portal\Http\Controllers;

use App\Domains\Portal\Models\PortalAccount;
use App\Domains\Portal\Services\Visibility\PortalAttachmentGuard;
use App\Support\Attachments\Attachment;
use App\Support\Attachments\AttachmentStorage;
use App\Support\Attachments\Exceptions\AttachmentInfectedException;
use App\Support\Attachments\Exceptions\AttachmentPendingScanException;
use App\Support\Attachments\ScanState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalAttachmentController
{
    public function __construct(private PortalAttachmentGuard $guard, private AttachmentStorage $storage) {}

    public function show(Request $request, string $attachment): JsonResponse|StreamedResponse|Response
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

        if ($attachment->scan_state === ScanState::Pending) {
            throw new AttachmentPendingScanException;
        }

        if (in_array($attachment->scan_state, [ScanState::Infected, ScanState::Failed], true)) {
            throw new AttachmentInfectedException;
        }

        return $this->storage->stream($attachment);
    }
}
