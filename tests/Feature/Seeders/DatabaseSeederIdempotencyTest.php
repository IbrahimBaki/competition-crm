<?php

namespace Tests\Feature\Seeders;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DatabaseSeederIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_is_idempotent(): void
    {
        Artisan::call('db:seed');

        $roleCounts = $this->getRowCounts();
        $firstRunRoles = $roleCounts['roles'];
        $firstRunBranches = $roleCounts['branches'];
        $firstRunWorkingHours = $roleCounts['branch_working_hours'];
        $firstRunAuthSettings = $roleCounts['auth_settings'];

        Artisan::call('db:seed');

        $secondRoleCounts = $this->getRowCounts();

        $this->assertEquals($firstRunRoles, $secondRoleCounts['roles'], 'Role count changed on second seed run');
        $this->assertEquals($firstRunBranches, $secondRoleCounts['branches'], 'Branch count changed on second seed run');
        $this->assertEquals($firstRunWorkingHours, $secondRoleCounts['branch_working_hours'], 'Working hour count changed on second seed run');
        $this->assertEquals($firstRunAuthSettings, $secondRoleCounts['auth_settings'], 'Auth settings count changed on second seed run');
    }

    private function getRowCounts(): array
    {
        return [
            'roles' => \DB::table('roles')->count(),
            'branches' => \DB::table('branches')->count(),
            'branch_working_hours' => \DB::table('branch_working_hours')->count(),
            'auth_settings' => \DB::table('auth_settings')->count(),
        ];
    }
}
