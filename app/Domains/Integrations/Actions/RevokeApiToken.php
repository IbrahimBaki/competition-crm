<?php

namespace App\Domains\Integrations\Actions;

use App\Domains\Integrations\Exceptions\ApiTokenAlreadyRevokedException;
use App\Domains\Integrations\Models\ApiToken;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

final class RevokeApiToken
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(ApiToken $token, ?Authenticatable $actor = null): void
    {
        if ($token->revoked_at !== null) {
            throw new ApiTokenAlreadyRevokedException('Token is already revoked');
        }

        \DB::transaction(function () use ($token, $actor) {
            $token->update([
                'revoked_at' => now(),
                'revoked_by_user_id' => $actor?->id,
            ]);

            $this->auditLogger->record(
                $actor,
                'integrations.api_token.revoked',
                $token,
                null,
                [
                    'name' => $token->name,
                    'token_prefix' => $token->token_prefix,
                ]
            );
        });
    }
}
