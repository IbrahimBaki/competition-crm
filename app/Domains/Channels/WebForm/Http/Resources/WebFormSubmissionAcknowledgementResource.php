<?php

namespace App\Domains\Channels\WebForm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebFormSubmissionAcknowledgementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'ticket_reference' => $this->ticketReference,
            'tracking_token' => $this->trackingToken,
            'was_duplicate' => $this->wasDuplicate,
            'status_url' => route('web-forms.status', ['trackingToken' => $this->trackingToken]),
        ];
    }
}
