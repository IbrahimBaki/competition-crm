<?php

namespace Tests\Feature\Seeders;

use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederProductionGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_throws_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DemoSeeder must not run in production.');

        (new DemoSeeder)->run();
    }

    public function test_demo_seeder_runs_in_development(): void
    {
        $this->app['env'] = 'local';

        try {
            (new DemoSeeder)->run();
            $this->assertTrue(true, 'DemoSeeder should not throw in development');
        } catch (\RuntimeException $e) {
            $this->fail('DemoSeeder should not throw in development: '.$e->getMessage());
        }
    }
}
