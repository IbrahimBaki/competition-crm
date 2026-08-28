<?php

namespace App\Domains\Customers\Http\Controllers;

use App\Domains\Customers\Actions\AddCustomerNote;
use App\Domains\Customers\Http\Requests\StoreCustomerNoteRequest;
use App\Domains\Customers\Http\Resources\CustomerNoteResource;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerNote;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CustomerNoteController extends Controller
{
    use AuthorizesRequests;

    public function index(Customer $customer, Request $request)
    {
        $this->authorize('viewAny', CustomerNote::class);

        $spec = CollectionQuerySpec::create()
            ->withSorts(['created_at', '-created_at'])
            ->withIncludes([]);

        $query = new CollectionQuery($request, $spec);
        $notes = $query->paginate($customer->notes());

        return ApiResponse::collection(
            $notes,
            $query->meta(),
            $query->meta()['filters'] ?? [],
            $request->input('sort'),
        );
    }

    public function store(Customer $customer, StoreCustomerNoteRequest $request, AddCustomerNote $action)
    {
        $this->authorize('create', CustomerNote::class);

        $note = $action->execute(
            $customer,
            $request->input('body'),
            $request->user(),
        );

        return ApiResponse::item(new CustomerNoteResource($note), 201);
    }

    public function destroy(Customer $customer, CustomerNote $note): JsonResponse
    {
        if ($note->customer_id !== $customer->id) {
            abort(404);
        }

        $this->authorize('delete', $note);

        $note->delete();

        return ApiResponse::noContent();
    }
}
