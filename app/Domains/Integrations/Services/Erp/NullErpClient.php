<?php

namespace App\Domains\Integrations\Services\Erp;

final class NullErpClient implements ErpClient
{
    public function fetchCustomerContext(string $externalCustomerRef): ?ErpCustomerContext
    {
        return null;
    }
}
