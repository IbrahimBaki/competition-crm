<?php

namespace Tests\Feature\Ticketing;

use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Ticketing\Models\TicketSavedView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithPermissions;
use Tests\TestCase;

class TicketSavedViewHttpTest extends TestCase
{
    use InteractsWithPermissions;
    use RefreshDatabase;

    private function agentWithTicketAccess(): User
    {
        $user = User::factory()->create();
        $this->grantPermission($user, PermissionKey::TICKETS_VIEW_OWN);

        return $user;
    }

    public function test_user_without_ticket_view_scope_cannot_list_views(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/v1/ticket-saved-views')->assertForbidden();
    }

    public function test_user_sees_own_views_and_shared_views_but_not_others_private_views(): void
    {
        $user = $this->agentWithTicketAccess();
        $mine = TicketSavedView::factory()->create(['user_id' => $user->id, 'name' => 'Mine']);
        $shared = TicketSavedView::factory()->create(['is_shared' => true, 'name' => 'Shared']);
        TicketSavedView::factory()->create(['name' => "Someone else's private view"]);

        $response = $this->actingAs($user)->getJson('/api/v1/ticket-saved-views');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Mine'));
        $this->assertTrue($names->contains('Shared'));
        $this->assertCount(2, $names);
    }

    public function test_user_can_create_a_saved_view(): void
    {
        $user = $this->agentWithTicketAccess();

        $response = $this->actingAs($user)->postJson('/api/v1/ticket-saved-views', [
            'name' => 'My Open Tickets',
            'query' => ['filter' => ['status' => 'open']],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.name', 'My Open Tickets');
        $response->assertJsonPath('data.is_shared', false);
        $this->assertDatabaseHas('ticket_saved_views', ['user_id' => $user->id, 'name' => 'My Open Tickets']);
    }

    public function test_duplicate_view_name_for_the_same_user_is_rejected(): void
    {
        $user = $this->agentWithTicketAccess();
        TicketSavedView::factory()->create(['user_id' => $user->id, 'name' => 'High Priority']);

        $response = $this->actingAs($user)->postJson('/api/v1/ticket-saved-views', [
            'name' => 'High Priority',
            'query' => ['filter' => ['priority' => 'high']],
        ]);

        $response->assertStatus(409);
        $this->assertEquals('ticket.saved_view_name_taken', $response->json('error.code'));
    }

    public function test_two_different_users_can_reuse_the_same_view_name(): void
    {
        $userA = $this->agentWithTicketAccess();
        $userB = $this->agentWithTicketAccess();
        TicketSavedView::factory()->create(['user_id' => $userA->id, 'name' => 'High Priority']);

        $response = $this->actingAs($userB)->postJson('/api/v1/ticket-saved-views', [
            'name' => 'High Priority',
            'query' => ['filter' => ['priority' => 'high']],
        ]);

        $response->assertCreated();
    }

    public function test_owner_can_update_their_view(): void
    {
        $user = $this->agentWithTicketAccess();
        $view = TicketSavedView::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

        $response = $this->actingAs($user)->patchJson("/api/v1/ticket-saved-views/{$view->uuid}", [
            'name' => 'New Name',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'New Name');
    }

    public function test_non_owner_cannot_update_or_delete_a_view(): void
    {
        $owner = $this->agentWithTicketAccess();
        $intruder = $this->agentWithTicketAccess();
        $view = TicketSavedView::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)
            ->patchJson("/api/v1/ticket-saved-views/{$view->uuid}", ['name' => 'Hijacked'])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->deleteJson("/api/v1/ticket-saved-views/{$view->uuid}")
            ->assertForbidden();
    }

    public function test_owner_can_delete_their_view(): void
    {
        $user = $this->agentWithTicketAccess();
        $view = TicketSavedView::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->deleteJson("/api/v1/ticket-saved-views/{$view->uuid}")->assertNoContent();
        $this->assertDatabaseMissing('ticket_saved_views', ['id' => $view->id]);
    }
}
