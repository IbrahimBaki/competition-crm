<?php

namespace Database\Seeders;

use App\Domains\Security\Models\Role;
use App\Domains\Security\Permissions\PermissionKey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(
            ['name' => Role::ADMINISTRATOR],
            [
                'uuid' => Str::uuid(),
                'display_name' => [
                    'ar' => 'مدير النظام',
                    'en' => 'Administrator',
                ],
                'is_system' => true,
            ]
        );

        $existingKeys = $adminRole->permissions()->pluck('permission_key')->toArray();
        $allKeys = PermissionKey::all();

        $keysToAdd = array_diff($allKeys, $existingKeys);
        $keysToRemove = array_diff($existingKeys, $allKeys);

        if (! empty($keysToAdd)) {
            $permissions = array_map(fn ($key) => ['permission_key' => $key], $keysToAdd);
            $adminRole->permissions()->createMany($permissions);
        }

        if (! empty($keysToRemove)) {
            $adminRole->permissions()
                ->whereIn('permission_key', $keysToRemove)
                ->delete();
        }
    }
}
