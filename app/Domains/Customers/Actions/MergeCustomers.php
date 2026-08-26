<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Exceptions\CannotMergeCustomerIntoItselfException;
use App\Domains\Customers\Exceptions\CustomerAlreadyMergedException;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerDuplicateCandidate;
use App\Domains\Customers\Models\CustomerEventType;
use App\Domains\Customers\Models\DuplicateCandidateStatus;
use App\Domains\Customers\Services\Merge\MergeRelationRegistry;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class MergeCustomers
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly MergeRelationRegistry $mergeRegistry,
    ) {}

    public function execute(
        Customer $survivor,
        Customer $loser,
        ?Authenticatable $actor = null,
    ): Customer {
        if ($survivor->id === $loser->id) {
            throw new CannotMergeCustomerIntoItselfException;
        }

        if ($survivor->isMerged() || $loser->isMerged()) {
            throw new CustomerAlreadyMergedException;
        }

        if ($survivor->anonymised_at !== null || $loser->anonymised_at !== null) {
            throw new CustomerAlreadyMergedException;
        }

        return \DB::transaction(function () use ($survivor, $loser, $actor) {
            // Lock both rows in ascending id order to prevent deadlocks
            $first = $survivor->id < $loser->id ? $survivor : $loser;
            $second = $survivor->id < $loser->id ? $loser : $survivor;

            Customer::whereIn('id', [$first->id, $second->id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            // Refresh to get latest state
            $survivor->refresh();
            $loser->refresh();

            // Re-check after locking
            if ($survivor->isMerged() || $loser->isMerged()) {
                throw new CustomerAlreadyMergedException;
            }

            $before = [
                'survivor_uuid' => $survivor->uuid,
                'loser_uuid' => $loser->uuid,
            ];

            $moved = [];

            // Move all relations
            foreach ($this->mergeRegistry->all() as $relation) {
                $count = $relation->moveTo($survivor, $loser);
                $moved[$relation->key()] = $count;
            }

            // Mark loser as merged
            $loser->update([
                'merged_into_customer_id' => $survivor->id,
                'merged_at' => now(),
            ]);

            // Record merge events
            $survivor->events()->create([
                'uuid' => Str::uuid(),
                'type' => CustomerEventType::Merged->value,
                'actor_user_id' => $actor?->id,
                'payload' => [
                    'merged_customer_uuid' => $loser->uuid,
                    'moved' => $moved,
                ],
                'occurred_at' => now(),
            ]);

            $loser->events()->create([
                'uuid' => Str::uuid(),
                'type' => CustomerEventType::MergedInto->value,
                'actor_user_id' => $actor?->id,
                'payload' => [
                    'merged_into_customer_uuid' => $survivor->uuid,
                ],
                'occurred_at' => now(),
            ]);

            // Update duplicate candidate status if exists
            CustomerDuplicateCandidate::where(function ($q) use ($survivor, $loser) {
                $q->where('customer_id', $survivor->id)->where('duplicate_customer_id', $loser->id)
                    ->orWhere('customer_id', $loser->id)->where('duplicate_customer_id', $survivor->id);
            })->update([
                'status' => DuplicateCandidateStatus::Merged->value,
                'reviewed_by_user_id' => $actor?->id,
                'reviewed_at' => now(),
            ]);

            $after = [
                'survivor_uuid' => $survivor->uuid,
                'loser_uuid' => $loser->uuid,
                'moved' => $moved,
            ];

            // Audit the merge
            $this->auditLogger->record(
                $actor,
                'customers.merged',
                $survivor,
                $before,
                $after
            );

            return $survivor;
        });
    }
}
