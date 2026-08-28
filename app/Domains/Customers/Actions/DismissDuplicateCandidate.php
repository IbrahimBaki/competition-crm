<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Models\CustomerDuplicateCandidate;
use App\Domains\Customers\Models\DuplicateCandidateStatus;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class DismissDuplicateCandidate
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(
        CustomerDuplicateCandidate $candidate,
        ?Authenticatable $actor = null,
    ): CustomerDuplicateCandidate {
        $candidate->update([
            'status' => DuplicateCandidateStatus::Dismissed->value,
            'reviewed_by_user_id' => $actor instanceof User ? $actor->id : null,
            'reviewed_at' => now(),
        ]);

        $this->auditLogger->record(
            $actor,
            'customers.duplicate.dismissed',
            $candidate->customer,
            null,
            [
                'duplicate_customer_uuid' => $candidate->duplicateCustomer->uuid,
            ]
        );

        return $candidate;
    }
}
