<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Master seeder for production-safe database bootstrap.
 *
 * Produces a fully operable organisation baseline:
 * - All permission keys registered
 * - Standard system roles (Administrator, Manager, Supervisor, Agent, Viewer)
 * - Branch calendars (HQ with Sun-Thu hours, OPS24 24/7)
 * - Auth settings with default locale
 * - Seed admin user (if SEED_ADMIN_EMAIL is set)
 *
 * Wrapped in a transaction to rollback on any partial failure.
 *
 * Demo data (sample users, departments, tickets) lives in DemoSeeder,
 * invoked separately: php artisan db:seed --class=Database\\Seeders\\DemoSeeder
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DB::transaction(function () {
            $this->call(PermissionsAndRolesSeeder::class);
            $this->call(AuthSettingsSeeder::class);
            $this->call(BranchCalendarsSeeder::class);
            $this->call(SlaPolicySeeder::class);
            $this->call(TicketCatalogueSeeder::class);
            $this->call(NotificationTemplatesSeeder::class);
            $this->call(SeedAdminUserSeeder::class);
        });
    }
}
