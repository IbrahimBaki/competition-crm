<?php

namespace Tests\Feature\Security;

use App\Domains\Organisation\Models\Department;
use App\Domains\Security\Permissions\Scope;
use App\Domains\Security\Scoping\OrganisationStructureScopeFilter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentScopeFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_filter_restricts_to_user_departments(): void
    {
        $user = User::factory()->create();
        $dept1 = Department::factory()->create(['name' => 'dept1', 'display_name' => ['ar' => 'قسم 1', 'en' => 'Dept 1']]);
        $dept2 = Department::factory()->create(['name' => 'dept2', 'display_name' => ['ar' => 'قسم 2', 'en' => 'Dept 2']]);

        $user->departments()->attach($dept1);

        $filter = new OrganisationStructureScopeFilter;
        $query = Department::query();
        $filtered = $filter->apply($query, $user, Scope::Department);

        $results = $filtered->get();

        $this->assertTrue($results->contains('id', $dept1->id));
        $this->assertFalse($results->contains('id', $dept2->id));
    }

    public function test_scope_filter_returns_all_with_any_scope(): void
    {
        $user = User::factory()->create();
        $dept1 = Department::factory()->create(['name' => 'dept1', 'display_name' => ['ar' => 'قسم 1', 'en' => 'Dept 1']]);
        $dept2 = Department::factory()->create(['name' => 'dept2', 'display_name' => ['ar' => 'قسم 2', 'en' => 'Dept 2']]);

        $filter = new OrganisationStructureScopeFilter;
        $query = Department::query();
        $filtered = $filter->apply($query, $user, Scope::Any);

        $results = $filtered->get();

        $this->assertTrue($results->contains('id', $dept1->id));
        $this->assertTrue($results->contains('id', $dept2->id));
    }
}
