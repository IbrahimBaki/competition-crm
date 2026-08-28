<?php

namespace App\Domains\Channels\Chat\Services\Identity;

use App\Domains\Channels\Chat\Models\ChatVisitorIdentity;
use App\Domains\Customers\Services\CustomerIntakeGuard;
use App\Domains\Customers\Services\Identity\CustomerIdentityResolver;
use Illuminate\Support\Str;

final class ResolveChatVisitor
{
    public function __construct(
        private readonly CustomerIdentityResolver $identityResolver,
        private readonly CustomerIntakeGuard $intakeGuard,
    ) {}

    public function handle(
        ?string $visitorToken,
        ?string $displayName,
        ?string $email,
        ?string $phone,
        ?string $locale,
        ?string $userAgent,
    ): ChatVisitorIdentity {
        $token = $visitorToken ?? Str::random(32);

        $identity = ChatVisitorIdentity::firstOrCreate(
            ['visitor_token' => $token],
            [
                'display_name' => $displayName,
                'email' => $email,
                'phone' => $phone,
                'locale' => $locale,
                'user_agent' => $userAgent,
            ]
        );

        if ($email || $phone) {
            $identities = [];
            if ($email) {
                $identities[] = ['type' => 'email', 'value' => $email];
            }
            if ($phone) {
                $identities[] = ['type' => 'phone', 'value' => $phone];
            }

            $resolution = $this->identityResolver->resolve($identities);
            if ($resolution->customer && ! $identity->customer_id) {
                $this->intakeGuard->assertCanOpenTicket($resolution->customer);
                $identity->update([
                    'customer_id' => $resolution->customer->id,
                    'email' => $email,
                    'phone' => $phone,
                ]);
            }
        }

        $identity->update(['last_seen_at' => now()]);

        return $identity;
    }
}
