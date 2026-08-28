<?php

namespace App\Domains\Customers\Http\Controllers;

use App\Domains\Customers\Actions\BlockCustomer;
use App\Domains\Customers\Actions\UnblockCustomer;
use App\Domains\Customers\Http\Requests\BlockCustomerRequest;
use App\Domains\Customers\Http\Resources\CustomerResource;
use App\Domains\Customers\Models\Customer;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

class CustomerBlockController extends Controller
{
    use AuthorizesRequests;

    public function block(Customer $customer, BlockCustomerRequest $request, BlockCustomer $action)
    {
        $this->authorize('block', $customer);

        $customer = $action->execute($customer, $request->input('reason'), $request->user());

        return ApiResponse::item(new CustomerResource($customer));
    }

    public function unblock(Customer $customer, UnblockCustomer $action)
    {
        $this->authorize('unblock', $customer);

        $customer = $action->execute($customer, auth()->user());

        return ApiResponse::item(new CustomerResource($customer));
    }
}
