<?php

namespace App\Domains\Integrations\Http\Controllers;

use App\Domains\Customers\Models\Customer;
use App\Domains\Integrations\Services\Erp\ErpClient;
use App\Support\Http\ApiResponse;

final class CustomerErpContextController
{
    public function show(Customer $customer, ErpClient $erpClient)
    {
        $context = $erpClient->fetchCustomerContext((string) $customer->external_ref ?? '');
        return ApiResponse::success([
            'customer_id' => $customer->uuid,
            'erp_context' => $context ? [
                'legal_name' => $context->legalName,
                'account_status' => $context->accountStatus,
                'credit_hold' => $context->creditHold,
                'outstanding_balance' => $context->outstandingBalance,
                'service_tier' => $context->serviceTier,
                'contract_end_date' => $context->contractEndDate,
            ] : null,
        ]);
    }
}
