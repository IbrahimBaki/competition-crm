<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerEventType;
use App\Domains\Customers\Services\TextNormaliser;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class UpdateCustomer
{
    public function __construct(
        private readonly TextNormaliser $normaliser,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(Customer $customer, array $data, ?Authenticatable $actor = null): Customer
    {
        return \DB::transaction(function () use ($customer, $data, $actor) {
            $before = $customer->toArray();

            if (isset($data['name'])) {
                $customer->name = $data['name'];
                $customer->name_normalised = $this->normaliser->normaliseName($data['name']);
            }

            if (isset($data['preferred_locale'])) {
                $customer->preferred_locale = $data['preferred_locale'];
            }

            if (isset($data['company_account_id'])) {
                $customer->company_account_id = $data['company_account_id'];
            }

            $customer->save();

            $after = $customer->toArray();

            if ($before !== $after) {
                $customer->events()->create([
                    'id' => Str::uuid(),
                    'type' => CustomerEventType::Updated->value,
                    'actor_user_id' => $actor?->id,
                    'payload' => ['changed_fields' => array_keys(array_diff_assoc($after, $before))],
                    'occurred_at' => now(),
                ]);

                $this->auditLogger->record(
                    $actor,
                    'customers.customer.updated',
                    $customer,
                    $before,
                    $after
                );
            }

            return $customer;
        });
    }
}
