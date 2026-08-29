<?php

namespace Tests\Feature\Knowledge;

use App\Domains\Knowledge\Models\ArticleState;
use App\Domains\Security\Models\Role;
use App\Domains\Security\Models\RolePermission;
use App\Models\User;
use Database\Factories\KnowledgeArticleFactory;
use Tests\TestCase;

class KnowledgeVersioningTest extends TestCase
{
    public function test_publish_creates_version(): void
    {
        $user = $this->createUserWithPermission('knowledge.articles.publish');
        $article = KnowledgeArticleFactory::new()->draft()->create(['author_id' => $user->id]);

        $this->actingAs($user)
            ->postJson("/api/v1/knowledge/articles/{$article->uuid}/state", [
                'state' => 'in_review',
            ]);

        $this->actingAs($user)
            ->postJson("/api/v1/knowledge/articles/{$article->uuid}/state", [
                'state' => 'published',
            ]);

        $this->assertDatabaseCount('knowledge_article_versions', 1);
        $article->refresh();
        $this->assertEquals(1, $article->current_version);
    }

    public function test_second_publish_increments_version(): void
    {
        $user = $this->createUserWithPermission('knowledge.articles.publish');
        $article = KnowledgeArticleFactory::new()->published()->create(['author_id' => $user->id]);

        // Back to draft
        $article->update(['state' => ArticleState::Draft->value]);

        // Publish again
        $this->actingAs($user)
            ->postJson("/api/v1/knowledge/articles/{$article->uuid}/state", [
                'state' => 'in_review',
            ]);

        $this->actingAs($user)
            ->postJson("/api/v1/knowledge/articles/{$article->uuid}/state", [
                'state' => 'published',
            ]);

        $this->assertDatabaseCount('knowledge_article_versions', 2);
        $article->refresh();
        $this->assertEquals(2, $article->current_version);
    }

    public function test_restore_version_does_not_mutate_version_row(): void
    {
        $user = $this->createUserWithPermission('knowledge.articles.versions.restore');
        $article = KnowledgeArticleFactory::new()->published()->create(['author_id' => $user->id]);

        $version = $article->versions()->first();
        $originalContent = $version->title;

        $this->actingAs($user)
            ->postJson("/api/v1/knowledge/articles/{$article->uuid}/versions/{$version->version}/restore");

        $version->refresh();
        $this->assertEquals($originalContent, $version->title);
    }

    public function test_restore_version_sets_article_to_draft(): void
    {
        $user = $this->createUserWithPermission('knowledge.articles.versions.restore');
        $article = KnowledgeArticleFactory::new()->published()->create(['author_id' => $user->id]);

        $version = $article->versions()->first();

        $this->actingAs($user)
            ->postJson("/api/v1/knowledge/articles/{$article->uuid}/versions/{$version->version}/restore");

        $article->refresh();
        $this->assertSame(ArticleState::Draft, $article->state);
    }

    private function createUserWithPermission(string $permission): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(
            ['name' => 'test-role'],
            ['display_name' => ['en' => 'Test role', 'ar' => 'دور اختباري'], 'is_system' => false],
        );
        RolePermission::firstOrCreate([
            'role_id' => $role->id,
            'permission_key' => $permission,
        ]);
        $user->roles()->attach($role);

        return $user;
    }
}
