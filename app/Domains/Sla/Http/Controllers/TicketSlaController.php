<?php

namespace App\Domains\Sla\Http\Controllers;

use App\Domains\Sla\Actions\ResetTicketSla;
use App\Domains\Sla\Http\Requests\ResetTicketSlaRequest;
use App\Domains\Sla\Http\Resources\TicketSlaResource;
use App\Domains\Sla\Models\TicketSlaClock;
use App\Support\Http\ApiResponse;
use Illuminate\Routing\Controller;

class TicketSlaController extends Controller
{
    public function show(TicketSlaClock $clock)
    {
        $this->authorize('view', $clock->ticket);

        return ApiResponse::ok(new TicketSlaResource($clock->getPosition()));
    }

    public function reset(ResetTicketSlaRequest $request, TicketSlaClock $clock, ResetTicketSla $action)
    {
        $this->authorize('reset', $clock->ticket);

        $clock = $action->handle($clock, $request->user(), $request->input('reason'));

        return ApiResponse::ok(new TicketSlaResource($clock->getPosition()));
    }
}
