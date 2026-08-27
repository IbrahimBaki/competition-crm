<?php

namespace App\Domains\Integrations\Services\Erp;

readonly class ErpCustomerContext
{
    public function __construct(
        public string $externalCustomerRef,
        public ?string $legalName = null,
        public ?string $accountStatus = null,
        public ?bool $creditHold = null,
        public ?string $currency = null,
        public ?float $outstandingBalance = null,
        public ?string $serviceTier = null,
        public ?string $lastInvoiceDate = null,
        public ?string $lastInvoiceRef = null,
        public ?string $contractEndDate = null,
        public ?string $sourceTimestamp = null,
    ) {}
}
