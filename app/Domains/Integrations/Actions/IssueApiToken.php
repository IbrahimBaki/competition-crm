<?php

namespace App\Domains\Integrations\Actions;

use App\Domains\Integrations\Exceptions\UnknownApiScopeException;
use App\Domains\Integrations\Models\ApiScope;
use App\Domains\Integrations\Models\ApiToken;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

final class IssueApiToken
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(
        string $name,
        array $scopeStrings,
        ?Authenticatable $actor = null,
    ): IssuedApiTokenDTO {
        return \DB::transaction(function () use ($name, $scopeStrings, $actor) {
            // Validate all scopes exist
            foreach ($scopeStrings as $scopeString) {
                try {
                    ApiScope::from($scopeString);
                } catch (\ValueError) {
                    throw new UnknownApiScopeException("Unknown scope: {$scopeString}");
                }
            }

            // Generate plaintext token (never persisted or logged)
            $plaintext = Str::random(32);
            $hash = hash('sha256', $plaintext);
            $prefix = substr($plaintext, 0, 8);

            $token = ApiToken::create([
                'uuid' => (string) Str::uuid(),
                'name' => $name,
                'token_hash' => $hash,
                'token_prefix' => $prefix,
                'scopes' => $scopeStrings,
                'created_by_user_id' => $actor?->id,
            ]);

            $this->auditLogger->record(
                $actor,
                'integrations.api_token.issued',
                $token,
                null,
                [
                    'name' => $token->name,
                    'scopes' => $token->scopes,
                    'token_prefix' => $token->token_prefix,
                ]
            );

            return new IssuedApiTokenDTO(
                uuid: $token->uuid,
                plaintext: $plaintext,
                name: $token->name,
                scopes: $token->scopes,
            );
        });
    }
}
