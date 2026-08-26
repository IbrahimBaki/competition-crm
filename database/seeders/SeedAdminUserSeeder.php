<?php

namespace Database\Seeders;

use App\Domains\Security\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SeedAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SEED_ADMIN_EMAIL');

        if (! $email) {
            $this->command?->warn('SEED_ADMIN_EMAIL not set; skipping seed admin user creation.');

            return;
        }

        $password = env('SEED_ADMIN_PASSWORD');
        $isProduction = app()->environment('production');

        if (! $password) {
            if ($isProduction) {
                $this->command?->warn('SEED_ADMIN_PASSWORD not set in production; skipping seed admin user creation.');

                return;
            }

            $password = Str::random(16);
            $this->command?->info("Generated seed admin password: {$password}");
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'uuid' => Str::uuid(),
                'name' => 'Admin User',
                'password' => $password,
                'locale' => 'ar',
                'email_verified_at' => now(),
            ]
        );

        if (! $user->roles()->where('name', Role::ADMINISTRATOR)->exists()) {
            $adminRole = Role::where('name', Role::ADMINISTRATOR)->first();
            if ($adminRole) {
                $user->roles()->attach($adminRole->id);
            }
        }
    }
}
