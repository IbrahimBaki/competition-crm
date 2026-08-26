<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthSettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('auth_settings')->delete();

        DB::table('auth_settings')->insert([
            'id' => 1,
            'require_two_factor' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
