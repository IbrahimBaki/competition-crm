<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds notification and email templates.
 *
 * Wire-up point for future story that creates notification_templates table.
 * When table exists, this seeder will install bilingual notification templates keyed on code:
 * - user.invited — invitation flow
 * - ticket.assigned — ticket assignment notification
 * - sla.breach.warning — SLA warning before breach
 * - (and more as needed)
 *
 * Each template has bilingual subject and body (ar + en) via BilingualString.
 * Reuses blade templates from resources/views/mail/ for consistency.
 *
 * @codeCoverageIgnore
 */
class NotificationTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('notification_templates')) {
            $this->command?->warn('Notification templates table not yet migrated; skipping templates.');

            return;
        }
    }
}
