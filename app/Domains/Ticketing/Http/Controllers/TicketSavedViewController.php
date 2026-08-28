<?php

namespace App\Domains\Ticketing\Http\Controllers;

use App\Domains\Ticketing\Exceptions\SavedViewNameTakenException;
use App\Domains\Ticketing\Http\Requests\StoreTicketSavedViewRequest;
use App\Domains\Ticketing\Http\Requests\UpdateTicketSavedViewRequest;
use App\Domains\Ticketing\Http\Resources\TicketSavedViewResource;
use App\Domains\Ticketing\Models\TicketSavedView;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TicketSavedViewController extends Controller
{
    use AuthorizesRequests;

    /**
     * The user's own views plus any shared org-wide, matching the
     * "System" and "Me" owner column in the Saved Views screen.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', TicketSavedView::class);

        $views = TicketSavedView::query()
            ->where('user_id', $request->user()->id)
            ->orWhere('is_shared', true)
            ->orderBy('name')
            ->paginate((int) $request->query('per_page', 25));

        return ApiResponse::collection($views->through(fn (TicketSavedView $v) => new TicketSavedViewResource($v)));
    }

    public function store(StoreTicketSavedViewRequest $request)
    {
        $this->authorize('create', TicketSavedView::class);

        $this->assertNameAvailable($request->user()->id, $request->validated('name'));

        $view = TicketSavedView::create([
            'user_id' => $request->user()->id,
            'name' => $request->validated('name'),
            'query' => $request->validated('query'),
            'is_shared' => $request->boolean('is_shared'),
        ]);

        return ApiResponse::created(new TicketSavedViewResource($view));
    }

    public function update(UpdateTicketSavedViewRequest $request, TicketSavedView $savedView)
    {
        $this->authorize('update', $savedView);

        if ($request->has('name') && $request->validated('name') !== $savedView->name) {
            $this->assertNameAvailable($savedView->user_id, $request->validated('name'));
        }

        $savedView->update($request->validated());

        return ApiResponse::ok(new TicketSavedViewResource($savedView));
    }

    public function destroy(TicketSavedView $savedView)
    {
        $this->authorize('delete', $savedView);

        $savedView->delete();

        return ApiResponse::noContent();
    }

    private function assertNameAvailable(int $userId, string $name): void
    {
        $taken = TicketSavedView::where('user_id', $userId)->where('name', $name)->exists();

        if ($taken) {
            throw new SavedViewNameTakenException;
        }
    }
}
