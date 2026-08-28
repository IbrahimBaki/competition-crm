<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerDuplicateCandidate;
use App\Domains\Customers\Models\DuplicateCandidateStatus;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class FlagDuplicateCustomers
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(
        Customer $customer1,
        Customer $customer2,
        string $rule,
        array $evidence = [],
        ?Authenticatable $actor = null,
    ): CustomerDuplicateCandidate {
        $primaryId = min($customer1->id, $customer2->id);
        $duplicateId = max($customer1->id, $customer2->id);

        $primaryCustomer = Customer::find($primaryId);
        $duplicateCustomer = Customer::find($duplicateId);

        return \DB::transaction(function () use (
            $primaryCustomer,
            $duplicateCustomer,
            $rule,
            $evidence,
            $actor,
        ) {
            $existing = CustomerDuplicateCandidate::where('customer_id', $primaryCustomer->id)
                ->where('duplicate_customer_id', $duplicateCustomer->id)
                ->first();

            if ($existing) {
                // Re-open dismissed candidates with different evidence
                if ($existing->status === DuplicateCandidateStatus::Dismissed && $existing->rule !== $rule) {
                    $existing->update([
                        'status' => DuplicateCandidateStatus::Pending->value,
                        'rule' => $rule,
                        'evidence' => $evidence,
                        'reviewed_by_user_id' => null,
                        'reviewed_at' => null,
                    ]);
                }

                return $existing;
            }

            $candidate = CustomerDuplicateCandidate::create([
                'uuid' => Str::uuid(),
                'customer_id' => $primaryCustomer->id,
                'duplicate_customer_id' => $duplicateCustomer->id,
                'status' => DuplicateCandidateStatus::Pending->value,
                'rule' => $rule,
                'evidence' => $evidence,
            ]);

            $this->auditLogger->record(
                $actor,
                'customers.duplicate.flagged',
                $primaryCustomer,
                null,
                [
                    'duplicate_customer_uuid' => $duplicateCustomer->uuid,
                    'rule' => $rule,
                    'evidence_count' => count($evidence),
                ]
            );

            return $candidate;
        });
    }
}
