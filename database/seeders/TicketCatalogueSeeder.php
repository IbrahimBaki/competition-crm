<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds ticket status, priority, and category catalogues.
 *
 * Wire-up point for future story that creates ticket_statuses, ticket_priorities, and ticket_categories tables.
 * When tables exist, this seeder will install:
 * - Status catalogue (open, pending, resolved, closed, etc.) with lifecycle types and bilingual names
 * - Priority set (low, medium, high, urgent) with bilingual names
 * - Category tree (parent + children structure) with bilingual names
 *
 * All rows keyed on `code` for idempotency via updateOrCreate.
 *
 * @codeCoverageIgnore
 */
class TicketCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('ticket_statuses')
            || ! Schema::hasTable('ticket_priorities')
            || ! Schema::hasTable('ticket_categories')) {
            $this->command?->warn('Ticket catalogue tables not yet migrated; skipping ticket catalogues.');

            return;
        }
    }
}
