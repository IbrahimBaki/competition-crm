<?php

namespace Tests\Feature\Seeders;

use Database\Seeders\AuthSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthSettingsIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_settings_preserves_customized_values_on_reseed(): void
    {
        $this->seed(AuthSettingsSeeder::class);

        DB::table('auth_settings')->update([
            'require_two_factor' => true,
            'default_locale' => 'en',
        ]);

        $beforeReseed = DB::table('auth_settings')->where('id', 1)->first();

        Artisan::call('db:seed', ['--class' => 'Database\Seeders\AuthSettingsSeeder']);

        $afterReseed = DB::table('auth_settings')->where('id', 1)->first();

        $this->assertEquals(1, $afterReseed->require_two_factor, 'require_two_factor should remain true after reseed');
        $this->assertEquals('en', $afterReseed->default_locale, 'default_locale should remain "en" after reseed');
    }

    public function test_auth_settings_has_default_values_on_fresh_seed(): void
    {
        $this->seed(AuthSettingsSeeder::class);

        $authSettings = DB::table('auth_settings')->where('id', 1)->firstOrFail();

        $this->assertEquals(0, $authSettings->require_two_factor);
        $this->assertEquals('ar', $authSettings->default_locale);
    }

    public function test_auth_settings_creates_exactly_one_row(): void
    {
        $this->seed(AuthSettingsSeeder::class);

        $count = DB::table('auth_settings')->count();

        $this->assertEquals(1, $count, 'Should have exactly one auth_settings row');
    }

    public function test_auth_settings_idempotent_multiple_reseeds(): void
    {
        $this->seed(AuthSettingsSeeder::class);

        DB::table('auth_settings')->update([
            'require_two_factor' => true,
            'default_locale' => 'en',
        ]);

        for ($i = 0; $i < 3; $i++) {
            Artisan::call('db:seed', ['--class' => 'Database\Seeders\AuthSettingsSeeder']);

            $authSettings = DB::table('auth_settings')->where('id', 1)->first();

            $this->assertEquals(1, $authSettings->require_two_factor);
            $this->assertEquals('en', $authSettings->default_locale);
            $this->assertEquals(1, DB::table('auth_settings')->count());
        }
    }
}
