<?php

namespace App\Domains\Workspace\Http\Controllers;

use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Workspace\Models\QuickReply;
use App\Domains\Workspace\Services\QuickReplyRenderer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

readonly class QuickReplyRenderController
{
    use AuthorizesRequests;

    public function __construct(private QuickReplyRenderer $renderer) {}

    public function store(Request $request, QuickReplyRenderer $renderer)
    {
        $validated = $request->validate(['reply_id' => 'required|uuid', 'ticket_id' => 'nullable|uuid']);
        $reply = QuickReply::where('uuid', $validated['reply_id'])->firstOrFail();
        $this->authorize('view', $reply);

        $ticket = $validated['ticket_id'] ? Ticket::where('uuid', $validated['ticket_id'])->first() : null;

        return response()->json([
            'data' => [
                'en' => $renderer->render($reply, $ticket, auth()->user(), 'en'),
                'ar' => $renderer->render($reply, $ticket, auth()->user(), 'ar'),
            ],
        ]);
    }
}
