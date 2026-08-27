<?php

namespace App\Domains\Portal\Http\Controllers;

use App\Domains\Portal\Actions\SubmitTicketFeedback;
use App\Domains\Portal\Exceptions\TicketFeedbackAlreadySubmittedException;
use App\Domains\Portal\Http\Resources\PortalTicketFeedbackResource;
use App\Domains\Portal\Models\TicketFeedbackInvitation;
use App\Domains\Portal\Services\Visibility\PortalTicketScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketFeedbackController
{
    public function __construct(
        private SubmitTicketFeedback $submitAction,
        private PortalTicketScope $ticketScope,
    ) {}

    public function store(Request $request, string $ticket): JsonResponse
    {
        $account = $request->user('portal');
        $validated = $request->validate([
            'score' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $ticket = $this->ticketScope->findOrFail($account, $ticket);

            $feedback = $this->submitAction->handle(
                $ticket,
                $validated['score'],
                $validated['comment'] ?? null,
                $account->customer,
                'portal'
            );

            return response()->json(['data' => new PortalTicketFeedbackResource($feedback)], 201);
        } catch (TicketFeedbackAlreadySubmittedException) {
            return response()->json([
                'error' => [
                    'code' => 'portal.feedback_already_submitted',
                    'message' => __('errors.portal.feedback_already_submitted'),
                ],
            ], 409);
        }
    }

    public function storeByInvitation(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'ticket_uuid' => ['required', 'string'],
            'score' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $tokenHash = hash('sha256', $token);
        $invitation = TicketFeedbackInvitation::where('token_hash', $tokenHash)->first();

        if (! $invitation || $invitation->consumed_at || now()->isAfter($invitation->expires_at)) {
            return response()->json([
                'error' => [
                    'code' => 'portal.feedback_invitation_invalid',
                    'message' => __('errors.portal.feedback_invitation_invalid'),
                ],
            ], 422);
        }

        try {
            $ticket = $invitation->ticket;

            $feedback = $this->submitAction->handle(
                $ticket,
                $validated['score'],
                $validated['comment'] ?? null,
                null,
                'link'
            );

            $invitation->update(['consumed_at' => now()]);

            return response()->json(['data' => new PortalTicketFeedbackResource($feedback)], 201);
        } catch (TicketFeedbackAlreadySubmittedException) {
            return response()->json([
                'error' => [
                    'code' => 'portal.feedback_already_submitted',
                    'message' => __('errors.portal.feedback_already_submitted'),
                ],
            ], 409);
        }
    }
}
