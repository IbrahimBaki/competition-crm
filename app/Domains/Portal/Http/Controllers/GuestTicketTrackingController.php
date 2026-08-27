<?php

namespace App\Domains\Portal\Http\Controllers;

use App\Domains\Portal\Http\Resources\PortalTicketResource;
use App\Domains\Portal\Models\PortalGuestGrant;
use Illuminate\Http\JsonResponse;

class GuestTicketTrackingController
{
    public function show(string $token): JsonResponse
    {
        $tokenHash = hash('sha256', $token);
        $grant = PortalGuestGrant::where('token_hash', $tokenHash)->first();

        if (! $grant || $grant->revoked_at !== null || now()->isAfter($grant->expires_at)) {
            return response()->json([
                'error' => [
                    'code' => 'portal.guest_grant_expired',
                    'message' => __('errors.portal.guest_grant_expired'),
                ],
            ], 410);
        }

        if (! $grant->ticket) {
            return response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Ticket not found',
                ],
            ], 404);
        }

        $grant->update(['last_used_at' => now()]);

        return response()->json([
            'data' => new PortalTicketResource($grant->ticket),
        ]);
    }
}
