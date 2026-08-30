<?php

namespace Database\Seeders;

use App\Domains\Customers\Models\Customer;
use App\Domains\Organisation\Models\Branch;
use App\Domains\Portal\Models\PortalAccount;
use App\Domains\Security\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class E2eTestSeeder extends Seeder
{
    public const PASSWORD = 'E2eSupport!2026';

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('E2eTestSeeder may only run in local or testing environments.');
        }

        $this->call(PermissionsAndRolesSeeder::class);
        $branch = Branch::where('code', 'HQ')->first();

        foreach ([
            'admin' => Role::ADMINISTRATOR,
            'agent' => Role::AGENT,
            'viewer' => Role::VIEWER,
        ] as $identity => $roleName) {
            $user = User::updateOrCreate(
                ['email' => "e2e.{$identity}@example.test"],
                [
                    'name' => "E2E {$identity}",
                    'password' => self::PASSWORD,
                    'locale' => 'en',
                ],
            );
            $user->forceFill([
                'email_verified_at' => now(),
                'deactivated_at' => null,
                'locked_until' => null,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ])->save();

            $role = Role::where('name', $roleName)->firstOrFail();
            $user->roles()->sync([$role->id]);
            if ($branch) {
                $user->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);
            }
        }

        $customer = Customer::updateOrCreate(
            ['uuid' => '00000000-0000-4000-8000-00000000e2e0'],
            ['name' => 'E2E Portal Customer', 'preferred_locale' => 'en'],
        );

        PortalAccount::updateOrCreate(
            ['email' => 'e2e.portal@example.test'],
            [
                'uuid' => '00000000-0000-4000-8000-00000000e2e1',
                'customer_id' => $customer->id,
                'password' => Hash::make(self::PASSWORD),
                'locale' => 'en',
                'email_verified_at' => now(),
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'deactivated_at' => null,
            ],
        );
    }
}
