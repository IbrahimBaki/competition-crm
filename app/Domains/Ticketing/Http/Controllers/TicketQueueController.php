<?php

namespace App\Domains\Ticketing\Http\Controllers;

use App\Domains\Organisation\Models\Department;
use App\Domains\Security\Scoping\OrganisationStructureScopeFilter;
use App\Domains\Ticketing\Models\Ticket;
use App\Domains\Ticketing\Services\TicketSearch;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class TicketQueueController extends Controller
{
    use AuthorizesRequests;

    public function department(
        Department $department,
        TicketSearch $search,
        OrganisationStructureScopeFilter $scopeFilter,
    ): JsonResponse {
        $this->authorize('viewQueue', Ticket::class);
        $scopeFilter->apply($department);

        $spec = (new CollectionQuerySpec)
            ->withSorts(['created_at', 'updated_at', 'priority', 'status', 'reference'])
            ->withFilters([
                'status' => ['eq'],
                'priority' => ['eq'],
                'assignee' => ['eq'],
                'category' => ['eq'],
                'tag' => ['eq'],
            ])
            ->withIncludes(['customer', 'department', 'category', 'assignee', 'tags'])
            ->withSearchableColumns(['subject_normalised', 'body_normalised', 'reference']);

        $collectionQuery = new CollectionQuery(request(), $spec);
        $query = Ticket::where('department_id', $department->id);

        if (request()->filled('filter.q')) {
            $search->apply($query, request()->input('filter.q'));
        }

        $query = $collectionQuery->applyTo($query);
        $paginated = $collectionQuery->paginate($query);

        return ApiResponse::collection($paginated)
            ->toResponse(request());
    }

    public function mine(
        TicketSearch $search,
    ): JsonResponse {
        $spec = (new CollectionQuerySpec)
            ->withSorts(['created_at', 'updated_at', 'priority', 'status', 'reference'])
            ->withFilters([
                'status' => ['eq'],
                'priority' => ['eq'],
                'department' => ['eq'],
                'category' => ['eq'],
                'tag' => ['eq'],
            ])
            ->withIncludes(['customer', 'department', 'category', 'assignee', 'tags'])
            ->withSearchableColumns(['subject_normalised', 'body_normalised', 'reference']);

        $collectionQuery = new CollectionQuery(request(), $spec);
        $query = Ticket::where('assigned_user_id', request()->user()->id);

        if (request()->filled('filter.q')) {
            $search->apply($query, request()->input('filter.q'));
        }

        $query = $collectionQuery->applyTo($query);
        $paginated = $collectionQuery->paginate($query);

        return ApiResponse::collection($paginated)
            ->toResponse(request());
    }
}
