<?php

namespace Tests\Unit\Seeders;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Security\Models\Role;
use Database\Seeders\BranchCalendarsSeeder;
use Database\Seeders\PermissionsAndRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BilingualCatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_seeded_roles_have_bilingual_display_names(): void
    {
        $this->seed(PermissionsAndRolesSeeder::class);

        $roles = Role::all();

        foreach ($roles as $role) {
            $displayName = $role->display_name;

            $this->assertIsArray($displayName, "Role {$role->name} display_name should be an array");
            $this->assertArrayHasKey('ar', $displayName, "Role {$role->name} display_name should have 'ar' key");
            $this->assertArrayHasKey('en', $displayName, "Role {$role->name} display_name should have 'en' key");
            $this->assertNotEmpty($displayName['ar'], "Role {$role->name} display_name['ar'] should not be empty");
            $this->assertNotEmpty($displayName['en'], "Role {$role->name} display_name['en'] should not be empty");
        }
    }

    public function test_all_seeded_branches_have_bilingual_names(): void
    {
        $this->seed(BranchCalendarsSeeder::class);

        $branches = Branch::all();

        foreach ($branches as $branch) {
            $this->assertNotNull($branch->name, "Branch {$branch->code} name should not be null");
            $this->assertNotEmpty($branch->name->ar, "Branch {$branch->code} Arabic name should not be empty");
            $this->assertNotEmpty($branch->name->en, "Branch {$branch->code} English name should not be empty");
        }
    }
}
