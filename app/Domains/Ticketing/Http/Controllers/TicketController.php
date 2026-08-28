<?php

namespace App\Domains\Ticketing\Http\Controllers;

use App\Domains\Customers\Models\Customer;
use App\Domains\Organisation\Models\Department;
use App\Domains\Ticketing\Actions\CreateTicket;
use App\Domains\Ticketing\Actions\UpdateTicket;
use App\Domains\Ticketing\Http\Requests\StoreTicketRequest;
use App\Domains\Ticketing\Http\Requests\UpdateTicketRequest;
use App\Domains\Ticketing\Http\Resources\TicketResource;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Models\TicketPriority;
use App\Domains\Ticketing\Services\TicketSearch;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    public function index(TicketSearch $search): JsonResponse
    {
        $spec = (new CollectionQuerySpec)
            ->withSorts(['created_at', 'updated_at', 'priority', 'status', 'reference'])
            ->withFilters([
                'status' => ['eq'],
                'priority' => ['eq'],
                'department' => ['eq'],
                'assignee' => ['eq'],
                'customer' => ['eq'],
                'category' => ['eq'],
                'tag' => ['eq'],
            ])
            ->withIncludes(['customer', 'department', 'category', 'assignee', 'tags'])
            ->withSearchableColumns(['subject_normalised', 'body_normalised', 'reference']);

        $collectionQuery = new CollectionQuery(request(), $spec);
        $query = Ticket::query();

        if (request()->filled('filter.q')) {
            $search->apply($query, request()->input('filter.q'));
        }

        $query = $collectionQuery->applyTo($query);
        $paginated = $collectionQuery->paginate($query);

        $counts = (clone $query)->reorder()
            ->selectRaw('status, count(*) as total')->groupBy('status')
            ->pluck('total', 'status')->toArray();

        $meta = array_merge($collectionQuery->meta(), [
            'counts' => [
                'status' => (object) $counts,
            ],
        ]);

        return ApiResponse::collection($paginated, $meta)
            ->toResponse(request());
    }

    public function show(Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        return ApiResponse::item(
            new TicketResource($ticket->load('customer', 'department', 'category', 'assignee', 'tags'))
        )->toResponse(request());
    }

    public function store(
        StoreTicketRequest $request,
        CreateTicket $action,
    ): JsonResponse {
        $customer = Customer::findByUuidOrFail($request->input('customer_uuid'));
        $department = Department::find($request->input('department_uuid'));

        $ticket = $action->handle(
            customer: $customer,
            department: $department,
            priority: TicketPriority::from($request->input('priority')),
            subject: $request->input('subject'),
            body: $request->input('body'),
            category: $request->filled('category_uuid')
                ? TicketCategory::where('uuid', $request->input('category_uuid'))->first()
                : null,
            assignee: $request->filled('assignee_uuid')
                ? User::where('uuid', $request->input('assignee_uuid'))->first()
                : null,
            tags: $request->input('tags'),
            customFields: $request->input('custom_fields'),
            actor: $request->user(),
        );

        return ApiResponse::item(new TicketResource($ticket), 201)
            ->toResponse(request());
    }

    public function update(
        UpdateTicketRequest $request,
        Ticket $ticket,
        UpdateTicket $action,
    ): JsonResponse {
        $updates = $request->validated();

        $ticket = $action->handle($ticket, $updates, $request->user());

        return ApiResponse::item(new TicketResource($ticket))
            ->toResponse(request());
    }

    public function history(Ticket $ticket): JsonResponse
    {
        $this->authorize('viewHistory', $ticket);

        $events = $ticket->events()->paginate(25);

        return ApiResponse::collection($events)->toResponse(request());
    }
}
