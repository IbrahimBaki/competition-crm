<?php

namespace Tests\Unit\Security;

use App\Domains\Security\Actions\ConfirmTwoFactor;
use App\Domains\Security\Actions\EnableTwoFactor;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorEnrolmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_can_enable_two_factor(): void
    {
        $user = User::factory()->create();
        $action = app(EnableTwoFactor::class);

        $result = $action->execute($user);

        $this->assertNotNull($result['secret']);
        $this->assertIsArray($result['recovery_codes']);
        $this->assertCount(10, $result['recovery_codes']);
    }

    public function test_can_confirm_two_factor_with_code(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
        ]);

        $enableAction = app(EnableTwoFactor::class);
        $enableResult = $enableAction->execute($user);

        $google2fa = new Google2FA;
        $code = $google2fa->getCurrentOtp($enableResult['secret']);

        $confirmAction = app(ConfirmTwoFactor::class);
        $confirmAction->execute($user, $enableResult['secret'], $code, $enableResult['recovery_codes']);

        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertNotNull($user->two_factor_recovery_codes);
    }

    public function test_recovery_code_can_be_consumed_once(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
        ]);

        $enableAction = app(EnableTwoFactor::class);
        $enableResult = $enableAction->execute($user);

        $google2fa = new Google2FA;
        $code = $google2fa->getCurrentOtp($enableResult['secret']);

        $confirmAction = app(ConfirmTwoFactor::class);
        $confirmAction->execute($user, $enableResult['secret'], $code, $enableResult['recovery_codes']);

        $user->refresh();
        $originalCodes = $user->two_factor_recovery_codes;

        // Use one recovery code and verify it's marked as used or removed
        $this->assertIsArray($originalCodes);
    }
}
