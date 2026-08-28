<?php

namespace App\Domains\Customers\Http\Controllers;

use App\Domains\Customers\Actions\MergeCustomers;
use App\Domains\Customers\Http\Requests\MergeCustomerRequest;
use App\Domains\Customers\Http\Resources\CustomerResource;
use App\Domains\Customers\Models\Customer;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

class CustomerMergeController extends Controller
{
    use AuthorizesRequests;

    public function store(Customer $customer, MergeCustomerRequest $request, MergeCustomers $action)
    {
        $this->authorize('merge', $customer);

        $loserUuid = $request->input('duplicate_customer_uuid');
        $loser = Customer::where('uuid', $loserUuid)->firstOrFail();

        $survivor = $action->execute($customer, $loser, auth()->user());

        return ApiResponse::item(new CustomerResource($survivor));
    }
}
