<?php

namespace App\Domains\Integrations\Services\Erp;

interface ErpClient
{
    public function fetchCustomerContext(string $externalCustomerRef): ?ErpCustomerContext;
}
