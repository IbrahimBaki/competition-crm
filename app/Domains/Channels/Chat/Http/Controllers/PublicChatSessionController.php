<?php

namespace App\Domains\Channels\Chat\Http\Controllers;

use App\Domains\Channels\Chat\Actions\RequestChatSession;
use App\Domains\Organisation\Models\Department;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicChatSessionController
{
    public function __construct(
        private readonly RequestChatSession $requestSession,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $dept = Department::findOrFail($request->input('department'));
        $result = $this->requestSession->handle(
            department: $dept,
            visitorToken: $request->input('visitor_token'),
            displayName: $request->input('display_name'),
            email: $request->input('email'),
            phone: $request->input('phone'),
            locale: $request->input('locale', 'en'),
            userAgent: $request->userAgent(),
        );

        return ApiResponse::item([
            'session_uuid' => $result->session->uuid,
            'visitor_token' => $result->visitorToken,
            'availability' => $result->availability->outcome->value,
            'queue_position' => $result->availability->queuePosition,
        ])->toResponse($request, 201);
    }
}
