<?php

namespace Tests\Feature\Knowledge;

use App\Domains\Knowledge\Models\KnowledgeCategory;
use App\Domains\Security\Models\Role;
use App\Domains\Security\Models\RolePermission;
use App\Models\User;
use Tests\TestCase;

class KnowledgeCategoryContractTest extends TestCase
{
    public function test_category_creation_accepts_and_returns_a_parent_uuid(): void
    {
        $user = $this->createManager();
        $parent = $this->createCategory('parent');

        $response = $this->actingAs($user)->postJson('/api/v1/knowledge/categories', [
            'parent_id' => $parent->uuid,
            'code' => 'child',
            'name' => ['en' => 'Child', 'ar' => 'فرعي'],
            'position' => 2,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.parent_id', $parent->uuid)
            ->assertJsonPath('data.depth', 2);

        $child = KnowledgeCategory::query()->where('code', 'child')->firstOrFail();
        $this->assertSame($parent->id, $child->parent_id);
    }

    public function test_category_update_uses_uuid_at_the_api_boundary(): void
    {
        $user = $this->createManager();
        $parent = $this->createCategory('parent');
        $category = $this->createCategory('movable');

        $this->actingAs($user)
            ->patchJson("/api/v1/knowledge/categories/{$category->uuid}", [
                'parent_id' => $parent->uuid,
            ])
            ->assertOk()
            ->assertJsonPath('data.parent_id', $parent->uuid)
            ->assertJsonPath('data.depth', 2);

        $this->assertSame($parent->id, $category->refresh()->parent_id);
    }

    private function createCategory(string $code): KnowledgeCategory
    {
        return KnowledgeCategory::query()->create([
            'code' => $code,
            'name' => ['en' => ucfirst($code), 'ar' => $code],
            'depth' => 1,
            'position' => 0,
            'is_active' => true,
        ]);
    }

    private function createManager(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'knowledge-category-manager',
            'display_name' => ['en' => 'Knowledge category manager', 'ar' => 'مدير تصنيفات المعرفة'],
            'is_system' => false,
        ]);
        RolePermission::query()->create([
            'role_id' => $role->id,
            'permission_key' => 'knowledge.categories.manage',
        ]);
        $user->roles()->attach($role);

        return $user;
    }
}
