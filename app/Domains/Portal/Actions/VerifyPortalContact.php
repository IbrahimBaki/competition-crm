<?php

namespace App\Domains\Portal\Actions;

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
    ) {}

    public function handle(string $plainToken): PortalAccount
    {
        return DB::transaction(function () use ($plainToken) {
            $tokenHash = hash('sha256', $plainToken);

            $verificationToken = PortalVerificationToken::where('token_hash', $tokenHash)
                ->firstOr(function () {
                    throw new PortalVerificationTokenInvalidException;
                });

            if ($verificationToken->consumed_at !== null) {
                throw new PortalVerificationTokenInvalidException;
            }

            if (now()->isAfter($verificationToken->expires_at)) {
                throw new PortalVerificationTokenInvalidException;
            }

            $account = $verificationToken->account;

            $resolution = $this->identityResolver->resolve([
                ['type' => 'email', 'value' => $account->email],
            ]);

            $customer = $resolution->customer;

            if ($customer !== null && $customer->blocked_at !== null) {
                throw new CustomerBlockedException($customer->blocked_reason ?? 'Unknown reason');
            }

            $account->update([
                'customer_id' => $customer?->id,
                'email_verified_at' => now(),
            ]);

            $verificationToken->update(['consumed_at' => now()]);

            return $account->fresh();
        });
    }
}
