<?php

namespace App\Domains\Customers\Http\Controllers;

use App\Domains\Customers\Actions\CreateCustomer;
use App\Domains\Customers\Actions\UpdateCustomer;
use App\Domains\Customers\Http\Requests\StoreCustomerRequest;
use App\Domains\Customers\Http\Requests\UpdateCustomerRequest;
use App\Domains\Customers\Http\Resources\CustomerResource;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Services\CustomerSearch;
use App\Support\Http\ApiResponse;
use App\Support\Http\CollectionQuery;
use App\Support\Http\CollectionQuerySpec;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, CustomerSearch $search)
    {
        $this->authorize('viewAny', Customer::class);

        $spec = CollectionQuerySpec::create()
            ->withSorts(['uuid', 'name', 'status', 'created_at', '-uuid', '-name', '-status', '-created_at'])
            ->withFilters(['status' => ['eq', 'neq']])
            ->withIncludes(['contacts'])
            ->withSearchableColumns([]);

        $query = new CollectionQuery($request, $spec);
        $baseQuery = Customer::query();

        if ($request->input('filter.q')) {
            $search->apply($baseQuery, $request->input('filter.q'));
        }

        $customers = $query->paginate($baseQuery);

        return ApiResponse::collection(
            $customers,
            $query->meta(),
            $query->meta()['filters'] ?? [],
            $request->input('sort'),
        );
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        return ApiResponse::item(new CustomerResource($customer->load('companyAccount', 'contacts')));
    }

    public function store(StoreCustomerRequest $request, CreateCustomer $action)
    {
        $this->authorize('create', Customer::class);

        $customer = $action->execute($request->validated(), $request->user());

        return ApiResponse::item(new CustomerResource($customer), 201);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer, UpdateCustomer $action)
    {
        $this->authorize('update', $customer);

        $customer = $action->execute($customer, $request->validated(), $request->user());

        return ApiResponse::item(new CustomerResource($customer));
    }
}
