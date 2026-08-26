<?php

namespace Tests\Unit\Security;

use App\Domains\Security\Actions\AuthenticateStaff;
use App\Domains\Security\Exceptions\AccountDeactivatedException;
use App\Domains\Security\Exceptions\AccountLockedException;
use App\Models\User;
use Database\Seeders\PermissionsAndRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticateStaffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionsAndRolesSeeder::class);
    }

    public function test_locks_account_after_10_failed_attempts(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CorrectPassword123!'),
            'failed_login_count' => 9,
        ]);

        $action = app(AuthenticateStaff::class);

        $this->expectException(AccountLockedException::class);
        $action->execute($user->email, 'WrongPassword123!', '127.0.0.1');

        $user->refresh();
        $this->assertNotNull($user->locked_until);
    }

    public function test_rejects_deactivated_user(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CorrectPassword123!'),
            'deactivated_at' => now(),
        ]);

        $action = app(AuthenticateStaff::class);

        $this->expectException(AccountDeactivatedException::class);
        $action->execute($user->email, 'CorrectPassword123!', '127.0.0.1');
    }

    public function test_clears_failed_count_on_success(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CorrectPassword123!'),
            'failed_login_count' => 5,
        ]);

        $action = app(AuthenticateStaff::class);

        $authenticatedUser = $action->execute($user->email, 'CorrectPassword123!', '127.0.0.1');

        $this->assertEquals(0, $authenticatedUser->failed_login_count);
        $this->assertNull($authenticatedUser->locked_until);
    }

    public function test_updates_last_login_timestamp(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        $action = app(AuthenticateStaff::class);

        $authenticatedUser = $action->execute($user->email, 'CorrectPassword123!', '127.0.0.1');

        $this->assertNotNull($authenticatedUser->last_login_at);
        $this->assertTrue($authenticatedUser->last_login_at->diffInSeconds(now()) < 5);
    }
}
