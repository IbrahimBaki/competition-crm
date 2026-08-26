<?php

namespace Tests\Feature\Organisation;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Security\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BranchTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\PermissionsAndRolesSeeder']);
        $this->admin = User::factory()->create(['email' => 'admin@example.com']);
        $adminRole = Role::where('name', Role::ADMINISTRATOR)->first();
        $this->admin->roles()->attach($adminRole);
    }

    public function test_can_create_branch(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/v1/branches', [
            'name' => ['ar' => 'الفرع الرئيسي', 'en' => 'Head Office'],
            'code' => 'hq',
            'timezone' => 'Africa/Cairo',
            'is_24_7' => false,
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'code',
                'timezone',
                'is_24_7',
                'is_active',
                'created_at',
                'updated_at',
            ],
            'meta' => ['request_id'],
        ]);
        $this->assertDatabaseHas('branches', [
            'code' => 'hq',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'organisation.branch.created',
        ]);
    }

    public function test_can_list_branches_with_pagination(): void
    {
        for ($i = 0; $i < 5; $i++) {
            Branch::create([
                'id' => Str::uuid(),
                'name' => ['ar' => "فرع {$i}", 'en' => "Branch {$i}"],
                'code' => "branch-{$i}",
                'timezone' => 'UTC',
            ]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/v1/branches');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'code'],
            ],
            'meta' => ['request_id', 'page', 'per_page', 'total', 'total_pages'],
            'links' => ['first', 'last', 'prev', 'next', 'self'],
        ]);
    }

    public function test_bilingual_name_returned_as_object(): void
    {
        $branch = Branch::create([
            'id' => Str::uuid(),
            'name' => ['ar' => 'فرع', 'en' => 'Branch'],
            'code' => 'test-branch',
            'timezone' => 'UTC',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')->getJson("/api/v1/branches/{$branch->id}");

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'name' => ['ar' => 'فرع', 'en' => 'Branch'],
            ],
        ]);
    }
}
