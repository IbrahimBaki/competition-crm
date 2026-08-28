<?php

namespace App\Support\Attachments\Http;

use App\Support\Attachments\Attachment;
use App\Support\Attachments\AttachmentStorage;
use App\Support\Attachments\Exceptions\AttachmentInfectedException;
use App\Support\Attachments\Exceptions\AttachmentPendingScanException;
use App\Support\Attachments\Resources\AttachmentResource;
use App\Support\Attachments\ScanState;
use App\Support\Http\ApiResponse;
use App\Support\Http\Uploads\Requests\StoreAttachmentRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AttachmentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly AttachmentStorage $storage,
    ) {}

    public function store(StoreAttachmentRequest $request): JsonResponse
    {
        Gate::authorize(constant('\App\Domains\Security\Permissions\PermissionKey::ATTACHMENTS_UPLOAD'));

        $attachment = $this->storage->store($request->file('file'), $request->user());

        return ApiResponse::item(new AttachmentResource($attachment), 201)->toResponse($request);
    }

    public function show(Attachment $attachment, Request $request): StreamedResponse
    {
        if (! Gate::allows('download', $attachment)) {
            throw new NotFoundHttpException;
        }

        if ($attachment->scan_state === ScanState::Pending) {
            throw new AttachmentPendingScanException;
        }

        if ($attachment->scan_state === ScanState::Infected || $attachment->scan_state === ScanState::Failed) {
            throw new AttachmentInfectedException;
        }

        return $this->storage->stream($attachment);
    }
}
