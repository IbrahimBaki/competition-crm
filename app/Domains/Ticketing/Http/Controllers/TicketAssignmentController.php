<?php

namespace App\Domains\Ticketing\Http\Controllers;

use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Actions\AssignTicket;
use App\Domains\Ticketing\Actions\ClaimTicket;
use App\Domains\Ticketing\Actions\TransferTicketToAgent;
use App\Domains\Ticketing\Actions\TransferTicketToDepartment;
use App\Domains\Ticketing\Actions\UnassignTicket;
use App\Domains\Ticketing\Http\Requests\AssignTicketRequest;
use App\Domains\Ticketing\Http\Requests\ClaimTicketRequest;
use App\Domains\Ticketing\Http\Requests\TransferTicketToAgentRequest;
use App\Domains\Ticketing\Http\Requests\TransferTicketToDepartmentRequest;
use App\Domains\Ticketing\Http\Resources\TicketResource;
use App\Domains\Ticketing\Models\Ticket;
use App\Models\User;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

class TicketAssignmentController extends Controller
{
    use AuthorizesRequests;

    public function assign(
        AssignTicketRequest $request,
        Ticket $ticket,
        AssignTicket $assign,
    ) {
        $this->authorize('assign', $ticket);

        $assignee = User::findByUuid($request->input('assignee_uuid'));

        $ticket = $assign->handle(
            $ticket,
            $assignee,
            $request->user(),
            $request->input('version'),
        );

        return ApiResponse::item(new TicketResource($ticket));
    }

    public function unassign(
        Ticket $ticket,
        UnassignTicket $unassign,
    ) {
        $this->authorize('assign', $ticket);

        $ticket = $unassign->handle($ticket, request()->user());

        return ApiResponse::item(new TicketResource($ticket));
    }

    public function claim(
        ClaimTicketRequest $request,
        Ticket $ticket,
        ClaimTicket $claim,
    ) {
        $this->authorize('claim', $ticket);

        $ticket = $claim->handle(
            $ticket,
            $request->user(),
            $request->input('version'),
        );

        return ApiResponse::item(new TicketResource($ticket));
    }

    public function transferToAgent(
        TransferTicketToAgentRequest $request,
        Ticket $ticket,
        TransferTicketToAgent $transfer,
    ) {
        $this->authorize('transferToAgent', $ticket);

        $assignee = User::findByUuid($request->input('user_uuid'));

        $ticket = $transfer->handle(
            $ticket,
            $assignee,
            $request->user(),
            $request->input('version'),
        );

        return ApiResponse::item(new TicketResource($ticket));
    }

    public function transferToDepartment(
        TransferTicketToDepartmentRequest $request,
        Ticket $ticket,
        TransferTicketToDepartment $transfer,
    ) {
        $this->authorize('transferToDepartment', $ticket);

        $department = Department::findOrFail($request->input('department_id'));

        $ticket = $transfer->handle(
            $ticket,
            $department,
            $request->user(),
            $request->boolean('keep_assignee', false),
            $request->input('version'),
        );

        return ApiResponse::item(new TicketResource($ticket));
    }
}
