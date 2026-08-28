<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthSettingsSeeder extends Seeder
{
    public function run(): void
    {
        if (! DB::table('auth_settings')->where('id', 1)->exists()) {
            DB::table('auth_settings')->insert([
                'id' => 1,
                'require_two_factor' => false,
                'default_locale' => 'ar',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
