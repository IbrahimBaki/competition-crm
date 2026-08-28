<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Http\Resources\AuditLogResource;
use App\Domains\Security\Models\AuditLog;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

class AuditLogController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $spec = CollectionQuerySpec::create()
            ->withSorts(['recorded_at', '-recorded_at'])
            ->withFilters([
                'actor_uuid' => ['eq'],
                'target_type' => ['eq'],
                'target_id' => ['eq'],
                'action' => ['eq'],
                'from' => ['gte'],
                'to' => ['lte'],
            ]);

        // Validate target_type against known allowed values
        $targetTypeFilter = $request->input('filter.target_type.eq');
        if ($targetTypeFilter !== null) {
            $allowedTargetTypes = ['User', 'Role', 'Branch', 'Department', 'Team', 'UserInvitation', 'BranchHoliday'];
            if (! in_array($targetTypeFilter, $allowedTargetTypes, true)) {
                throw ValidationException::withMessages([
                    'filter.target_type' => ['Invalid target_type: '.$targetTypeFilter],
                ]);
            }
        }

        $query = new CollectionQuery($request, $spec);
        $baseQuery = AuditLog::query();

        // Apply filters and sorting
        $baseQuery = $query->applyTo($baseQuery);

        // Apply default sort if none specified
        if (! $request->input('sort')) {
            $baseQuery = $baseQuery->orderByDesc('recorded_at');
        }

        // Paginate the final query
        $paginator = $baseQuery->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
            ->appends($request->query());

        // Transform items to resources
        $data = $paginator->setCollection(
            $paginator->getCollection()->map(fn ($item) => new AuditLogResource($item))
        );

        return ApiResponse::collection(
            $data,
            $query->meta(),
            $query->meta()['filters'] ?? [],
            $request->input('sort'),
        );
    }
}
