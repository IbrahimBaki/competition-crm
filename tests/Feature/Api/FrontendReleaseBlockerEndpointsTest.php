<?php

namespace Tests\Feature\Api;

use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Tests\Support\InteractsWithPermissions;
use Tests\TestCase;

class FrontendReleaseBlockerEndpointsTest extends TestCase
{
    use InteractsWithPermissions;

    public function test_inbound_email_list_uses_the_standard_page_envelope(): void
    {
        $user = User::factory()->create();
        $this->grantPermission($user, PermissionKey::CHANNELS_EMAIL_REPLAY_LIST);

        $this->actingAs($user)
            ->getJson('/api/v1/channels/email/inbound?page=1&per_page=25')
            ->assertOk()
            ->assertJsonPath('meta.page', 1)
            ->assertJsonPath('meta.per_page', 25)
            ->assertJsonPath('meta.total', 0)
            ->assertJsonPath('data', []);
    }

    public function test_retention_uses_its_registered_permission(): void
    {
        $user = User::factory()->create();
        $this->grantPermission($user, PermissionKey::DATAPROTECTION_RETENTION_VIEW);

        $this->actingAs($user)
            ->getJson('/api/v1/data-protection/retention')
            ->assertOk()
            ->assertJsonStructure(['data' => ['classes', 'audit_minimum_days']]);
    }

    public function test_ai_suggestion_list_uses_its_registered_permission(): void
    {
        $user = User::factory()->create();
        $this->grantPermission($user, PermissionKey::AI_SUGGESTIONS_RESOLVE);

        $this->actingAs($user)
            ->getJson('/api/v1/ai/suggestions')
            ->assertOk()
            ->assertJsonPath('data', []);
    }
}
