<?php

namespace App\Domains\Customers\Http\Controllers;

use App\Domains\Customers\Actions\DismissDuplicateCandidate;
use App\Domains\Customers\Http\Resources\CustomerDuplicateCandidateResource;
use App\Domains\Customers\Models\CustomerDuplicateCandidate;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CustomerDuplicateController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', CustomerDuplicateCandidate::class);

        $spec = CollectionQuerySpec::create()
            ->withFilters(['status' => ['eq']]);

        $query = new CollectionQuery($request, $spec);
        $candidates = $query->paginate(
            CustomerDuplicateCandidate::query()->orderBy('created_at', 'desc')
        );

        return ApiResponse::collection(
            $candidates,
            $query->meta(),
            $query->meta()['filters'] ?? [],
            $request->input('sort'),
        );
    }

    public function dismiss(CustomerDuplicateCandidate $candidate, DismissDuplicateCandidate $action)
    {
        $this->authorize('dismissDuplicate', $candidate);

        $candidate = $action->execute($candidate, auth()->user());

        return ApiResponse::item(new CustomerDuplicateCandidateResource($candidate));
    }
}
