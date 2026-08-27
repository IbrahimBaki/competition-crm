<?php

namespace App\Domains\Portal\Actions;

use App\Domains\Customers\Actions\CreateCustomer;
use App\Domains\Customers\Exceptions\CustomerBlockedException;
use App\Domains\Customers\Services\Identity\CustomerIdentityResolver;
use App\Domains\Portal\Exceptions\PortalVerificationTokenInvalidException;
use App\Domains\Portal\Models\PortalAccount;
use App\Domains\Portal\Models\PortalVerificationToken;
use Illuminate\Support\Facades\DB;

class VerifyPortalContact
{
    public function __construct(
        private CustomerIdentityResolver $identityResolver,
        private CreateCustomer $createCustomer,
    ) {}

    public function handle(string $plainToken): PortalAccount
    {
        return DB::transaction(function () use ($plainToken) {
            $tokenHash = hash('sha256', $plainToken);

            $verificationToken = PortalVerificationToken::where('token_hash', $tokenHash)
                ->firstOrFail(new PortalVerificationTokenInvalidException);

            if ($verificationToken->consumed_at !== null) {
                throw new PortalVerificationTokenInvalidException;
            }

            if (now()->isAfter($verificationToken->expires_at)) {
                throw new PortalVerificationTokenInvalidException;
            }

            $account = $verificationToken->account;

            $customer = $this->identityResolver->resolveByEmail($account->email);

            if ($customer === null) {
                $customer = $this->createCustomer->handle(
                    contacts: [['channel' => 'email', 'value' => $account->email]],
                );
            }

            if ($customer->blocked_at !== null) {
                throw new CustomerBlockedException($customer->blocked_reason ?? 'Unknown reason');
            }

            $account->update([
                'customer_id' => $customer->id,
                'email_verified_at' => now(),
            ]);

            $verificationToken->update(['consumed_at' => now()]);

            return $account->fresh();
        });
    }
}
