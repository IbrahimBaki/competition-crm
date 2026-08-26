<?php

namespace App\Domains\Customers\Http\Controllers;

use App\Domains\Customers\Actions\DismissDuplicateCandidate;
use App\Domains\Customers\Http\Resources\CustomerDuplicateCandidateResource;
use App\Domains\Customers\Models\CustomerDuplicateCandidate;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

class CustomerDuplicateController extends Controller
{
    use AuthorizesRequests;

    public function index(CollectionQuery $query)
    {
        $this->authorize('viewDuplicates', CustomerDuplicateCandidate::class);

        $candidates = $query->paginate(
            CustomerDuplicateCandidate::query()
                ->when($query->filter('status'), fn ($q, $status) => $q->where('status', $status))
                ->orderBy('created_at', 'desc')
        );

        return ApiResponse::collection(
            CustomerDuplicateCandidateResource::collection($candidates)
        );
    }

    public function dismiss(CustomerDuplicateCandidate $candidate, DismissDuplicateCandidate $action)
    {
        $this->authorize('dismissDuplicate', $candidate);

        $candidate = $action->execute($candidate, auth()->user());

        return ApiResponse::item(new CustomerDuplicateCandidateResource($candidate));
    }
}
