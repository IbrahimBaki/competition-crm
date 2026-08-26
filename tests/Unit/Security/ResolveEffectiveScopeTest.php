<?php

namespace Tests\Unit\Security;

use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;
use App\Domains\Security\Permissions\Scope;
use App\Domains\Security\Scoping\ResolveEffectiveScope;
use App\Models\User;
use Tests\TestCase;

class ResolveEffectiveScopeTest extends TestCase
{
    private ResolveEffectiveScope $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new ResolveEffectiveScope;
    }

    public function test_returns_any_when_user_has_any_scope(): void
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'editor',
            'display_name' => ['ar' => 'محرر', 'en' => 'Editor'],
        ]);

        $role->permissions()->create([
            'permission_key' => PermissionKey::TICKETS_VIEW_ANY,
        ]);

        $user->roles()->attach($role);

        $result = $this->resolver->resolve($user->fresh(), 'tickets', 'view');

        $this->assertEquals(Scope::Any, $result);
    }

    public function test_returns_department_when_user_has_department_and_team_scopes(): void
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'editor',
            'display_name' => ['ar' => 'محرر', 'en' => 'Editor'],
        ]);

        $role->permissions()->createMany([
            ['permission_key' => PermissionKey::TICKETS_VIEW_TEAM],
            ['permission_key' => PermissionKey::TICKETS_VIEW_DEPARTMENT],
        ]);

        $user->roles()->attach($role);

        $result = $this->resolver->resolve($user->fresh(), 'tickets', 'view');

        $this->assertEquals(Scope::Department, $result);
    }

    public function test_returns_null_when_user_has_no_matching_keys(): void
    {
        $user = User::factory()->create();

        $result = $this->resolver->resolve($user, 'tickets', 'view');

        $this->assertNull($result);
    }
}
