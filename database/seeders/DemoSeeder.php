<?php

namespace Database\Seeders;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Security\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seed demo data for manual testing and development.
 *
 * This seeder is NOT called from DatabaseSeeder.
 * Invoke explicitly with: php artisan db:seed --class=Database\\Seeders\\DemoSeeder
 *
 * HARD GUARD: throws RuntimeException in production.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('DemoSeeder must not run in production.');
        }

        $this->createDemoUsers();
    }

    private function createDemoUsers(): void
    {
        $hqBranch = Branch::where('code', 'HQ')->first();

        if (! $hqBranch) {
            $this->command?->warn('HQ branch not found; skipping demo user creation.');

            return;
        }

        $roles = [
            Role::ADMINISTRATOR => ['ar' => 'مدير النظام', 'en' => 'Admin Demo'],
            Role::MANAGER => ['ar' => 'مدير', 'en' => 'Manager Demo'],
            Role::SUPERVISOR => ['ar' => 'مشرف', 'en' => 'Supervisor Demo'],
            Role::AGENT => ['ar' => 'موظف دعم', 'en' => 'Agent Demo'],
            Role::VIEWER => ['ar' => 'مطّلع', 'en' => 'Viewer Demo'],
        ];

        foreach ($roles as $roleName => $roleDisplayName) {
            $user = User::factory()->create([
                'name' => $roleDisplayName['en'],
                'email' => strtolower($roleName).'+demo@example.com',
                'locale' => 'ar',
            ]);

            $role = Role::where('name', $roleName)->first();
            if ($role && ! $user->roles()->where('name', $roleName)->exists()) {
                $user->roles()->attach($role->id);
            }

            $user->branches()->attach($hqBranch->id, ['is_primary' => true]);
        }
    }
}
