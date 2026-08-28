<?php

namespace App\Domains\Channels\WebForm\Http\Controllers;

use App\Domains\Channels\WebForm\Http\Requests\SubmitWebFormRequest;
use App\Domains\Channels\WebForm\Http\Resources\PublicWebFormResource;
use App\Domains\Channels\WebForm\Http\Resources\WebFormStatusResource;
use App\Domains\Channels\WebForm\Http\Resources\WebFormSubmissionAcknowledgementResource;
use App\Domains\Channels\WebForm\Models\WebForm;
use App\Domains\Channels\WebForm\Models\WebFormSubmission;
use App\Domains\Channels\WebForm\Services\Intake\SubmitWebForm;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicWebFormController
{
    public function __construct(
        private readonly SubmitWebForm $submitWebForm,
    ) {}

    public function show(string $formKey): JsonResponse
    {
        $form = WebForm::where('key', $formKey)->first();

        if (! $form || ! $form->is_active) {
            throw new NotFoundHttpException;
        }

        return ApiResponse::item(new PublicWebFormResource($form))
            ->toResponse(request());
    }

    public function store(SubmitWebFormRequest $request, string $formKey): JsonResponse
    {
        $form = WebForm::where('key', $formKey)->first();

        if (! $form || ! $form->is_active) {
            throw new NotFoundHttpException;
        }

        $ipHash = hash('sha256', $request->ip() ?? '');
        $userAgent = $request->userAgent();

        $result = $this->submitWebForm->handle(
            form: $form,
            payload: $request->input('answers', []),
            ipHash: $ipHash,
            userAgent: $userAgent,
        );

        // created() sets 201; toResponse() takes no status argument, so the
        // previous `toResponse(request(), 201)` silently returned 200.
        return ApiResponse::created(new WebFormSubmissionAcknowledgementResource($result))
            ->toResponse(request());
    }

    public function status(string $trackingToken): JsonResponse
    {
        $submission = WebFormSubmission::where('tracking_token', $trackingToken)->first();

        if (! $submission) {
            throw new NotFoundHttpException;
        }

        return ApiResponse::item(new WebFormStatusResource($submission))
            ->toResponse(request());
    }
}
