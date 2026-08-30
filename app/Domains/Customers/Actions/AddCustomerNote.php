<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\CustomerEventType;
use App\Domains\Customers\Models\CustomerNote;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class AddCustomerNote
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(
        Customer $customer,
        string $body,
        ?Authenticatable $actor = null,
    ): CustomerNote {
        $actorId = $actor instanceof User ? $actor->id : null;

        return \DB::transaction(function () use ($customer, $body, $actor, $actorId) {
            $note = CustomerNote::create([
                'customer_id' => $customer->id,
                'author_user_id' => $actorId,
                'body' => $body,
            ]);

            $customer->events()->create([
                'id' => Str::uuid(),
                'type' => CustomerEventType::NoteAdded->value,
                'actor_user_id' => $actor->id,
                'occurred_at' => now(),
            ]);

            $this->auditLogger->record(
                $actor,
                'customers.note.added',
                $customer,
                null,
                $note->toArray()
            );

            return $note;
        });
    }
}
