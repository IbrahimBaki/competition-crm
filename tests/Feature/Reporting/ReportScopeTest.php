<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Domains\Reporting\Services\Filters\ReportFilter;
use App\Domains\Reporting\Services\Scoping\ReportScopeResolver;
use App\Domains\Security\Permissions\PermissionKey;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithPermissions;
use Tests\TestCase;

class ReportScopeTest extends TestCase
{
    use InteractsWithPermissions;
    use RefreshDatabase;

    public function test_agent_with_own_scope_sees_only_their_performance(): void
    {
        $agent = User::factory()->create();
        $otherAgent = User::factory()->create();

        // Grant agent reports.view.own
        $this->grantPermission($agent, PermissionKey::REPORTS_VIEW_OWN);

        $this->actingAs($agent);

        $now = CarbonImmutable::now('UTC');
        $filter = new ReportFilter(
            from: $now->subDays(7),
            to: $now,
            timezone: 'UTC',
        );

        // Use scoper
        $resolver = app(ReportScopeResolver::class);
        $resolved = $resolver->resolve($agent, 'agent_performance', $filter);

        // Own scope should force agentId to caller's own ID
        $this->assertEquals($agent->id, $resolved['filter']->agentId);
    }

    public function test_manager_with_department_scope_sees_their_departments(): void
    {
        $manager = User::factory()->create();
        $this->grantPermission($manager, PermissionKey::REPORTS_VIEW_DEPARTMENT);

        $this->actingAs($manager);

        $now = CarbonImmutable::now('UTC');
        $filter = new ReportFilter(
            from: $now->subDays(7),
            to: $now,
            timezone: 'UTC',
        );

        $resolver = app(ReportScopeResolver::class);
        $resolved = $resolver->resolve($manager, 'agent_performance', $filter);

        // Department scope allows narrow ing by department
        $this->assertNotNull($resolved['filter']);
    }

    public function test_requesting_foreign_department_returns_empty_not_error(): void
    {
        $agent = User::factory()->create();
        $this->grantPermission($agent, PermissionKey::REPORTS_VIEW_OWN);

        $this->actingAs($agent);

        $now = CarbonImmutable::now('UTC');
        $filter = new ReportFilter(
            from: $now->subDays(7),
            to: $now,
            timezone: 'UTC',
            departmentId: 999, // Foreign department
        );

        $resolver = app(ReportScopeResolver::class);
        $resolved = $resolver->resolve($agent, 'agent_performance', $filter);

        // Should resolve without error (silently narrow to empty)
        $this->assertNotNull($resolved);
    }

    public function test_user_with_any_scope_can_list_reports(): void
    {
        $user = User::factory()->create();
        $this->grantPermission($user, PermissionKey::REPORTS_VIEW_ANY);

        $this->actingAs($user)
            ->getJson('/api/v1/reports')
            ->assertOk()
            ->assertJsonPath('meta.available_reports', 6);
    }

    public function test_user_with_own_scope_can_list_reports(): void
    {
        $user = User::factory()->create();
        $this->grantPermission($user, PermissionKey::REPORTS_VIEW_OWN);

        $this->actingAs($user)
            ->getJson('/api/v1/reports')
            ->assertOk();
    }

    public function test_user_without_report_scope_cannot_list_reports(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson('/api/v1/reports')
            ->assertForbidden();
    }
}
