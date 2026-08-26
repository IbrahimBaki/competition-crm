<?php

namespace App\Domains\Customers\Http\Controllers;

use App\Domains\Customers\Actions\AddCustomerContact;
use App\Domains\Customers\Actions\RemoveCustomerContact;
use App\Domains\Customers\Http\Requests\StoreCustomerContactRequest;
use App\Domains\Customers\Http\Requests\UpdateCustomerContactRequest;
use App\Domains\Customers\Http\Resources\CustomerContactResource;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerContact;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class CustomerContactController extends Controller
{
    use AuthorizesRequests;

    public function index(Customer $customer, Request $request)
    {
        $this->authorize('viewAny', CustomerContact::class);

        $spec = CollectionQuerySpec::create()
            ->withSorts(['created_at', '-created_at', 'is_primary', '-is_primary'])
            ->withIncludes([]);

        $query = new CollectionQuery($request, $spec);
        $contacts = $query->paginate($customer->contacts());

        return ApiResponse::collection(
            $contacts,
            $query->meta(),
            $query->meta()['filters'] ?? [],
            $request->input('sort'),
        );
    }

    public function store(Customer $customer, StoreCustomerContactRequest $request, AddCustomerContact $action)
    {
        $this->authorize('create', CustomerContact::class);

        $contact = $action->execute(
            $customer,
            $request->input('type'),
            $request->input('value'),
            $request->input('label'),
            (bool) $request->input('is_primary', false),
            $request->user(),
        );

        return ApiResponse::item(new CustomerContactResource($contact), 201);
    }

    public function update(
        Customer $customer,
        CustomerContact $contact,
        UpdateCustomerContactRequest $request,
    ): JsonResponse {
        if ($contact->customer_id !== $customer->id) {
            abort(404);
        }

        $this->authorize('update', $contact);

        $contact->update($request->validated());

        return ApiResponse::item(new CustomerContactResource($contact));
    }

    public function destroy(Customer $customer, CustomerContact $contact, RemoveCustomerContact $action): JsonResponse
    {
        if ($contact->customer_id !== $customer->id) {
            abort(404);
        }

        $this->authorize('delete', $contact);

        $action->execute($customer, $contact, auth()->user());

        return ApiResponse::noContent();
    }
}
