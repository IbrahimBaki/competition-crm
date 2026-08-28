<?php

namespace App\Domains\Integrations\Services\Erp;

use App\Domains\Integrations\Exceptions\IntegrationDependencyUnavailableException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class HttpErpClient implements ErpClient
{
    public function fetchCustomerContext(string $externalCustomerRef): ?ErpCustomerContext
    {
        $baseUrl = config('integrations.erp.base_url');
        $apiKey = config('integrations.erp.api_key');
        $timeout = config('integrations.erp.timeout_seconds', 5);
        $cacheTtl = config('integrations.erp.cache_ttl_seconds', 300);

        if (!$baseUrl || !$apiKey) {
            return null;
        }

        $cacheKey = "erp_customer:{$externalCustomerRef}";

        try {
            return Cache::remember($cacheKey, $cacheTtl, function () use ($baseUrl, $apiKey, $timeout, $externalCustomerRef) {
                $response = Http::timeout($timeout)
                    ->withHeaders(['Authorization' => "Bearer {$apiKey}"])
                    ->get("{$baseUrl}/customers/{$externalCustomerRef}");

                if ($response->notFound()) {
                    return null;
                }

                if (!$response->successful()) {
                    throw new IntegrationDependencyUnavailableException('ERP service unavailable');
                }

                $data = $response->json();

                return new ErpCustomerContext(
                    externalCustomerRef: $externalCustomerRef,
                    legalName: $data['legal_name'] ?? null,
                    accountStatus: $data['account_status'] ?? null,
                    creditHold: $data['credit_hold'] ?? null,
                    currency: $data['currency'] ?? null,
                    outstandingBalance: $data['outstanding_balance'] ?? null,
                    serviceTier: $data['service_tier'] ?? null,
                    lastInvoiceDate: $data['last_invoice_date'] ?? null,
                    lastInvoiceRef: $data['last_invoice_ref'] ?? null,
                    contractEndDate: $data['contract_end_date'] ?? null,
                    sourceTimestamp: $data['source_timestamp'] ?? null,
                );
            });
        } catch (\Throwable $e) {
            if ($e instanceof IntegrationDependencyUnavailableException) {
                throw $e;
            }
            throw new IntegrationDependencyUnavailableException('ERP integration failed: ' . $e->getMessage());
        }
    }
}
