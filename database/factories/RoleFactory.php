<?php

namespace Database\Factories;

use App\Domains\Security\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        // `uuid` is filled by Role::boot(); `name` is unique, so keep it so.
        $name = 'role_'.$this->faker->unique()->numerify('########');

        return [
            'name' => $name,
            'display_name' => [
                'ar' => 'دور '.$name,
                'en' => 'Role '.$name,
            ],
            'is_system' => false,
        ];
    }

    /**
     * A system role — matches what PermissionsAndRolesSeeder creates.
     * Note reconcilePermissions() only syncs roles flagged is_system.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_system' => true,
        ]);
    }
}
