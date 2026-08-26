<?php

namespace Database\Seeders;

use App\Domains\Ticketing\Models\TicketCategory;
use App\Support\I18n\BilingualString;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Seeds ticket category catalogues.
 *
 * Creates a sample three-level category tree with custom fields.
 *
 * @codeCoverageIgnore
 */
class TicketCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('ticket_categories')) {
            $this->command?->warn('Ticket catalogue tables not yet migrated; skipping ticket catalogues.');

            return;
        }

        $support = TicketCategory::firstOrCreate(
            ['code' => 'support'],
            [
                'uuid' => Str::uuid(),
                'name' => new BilingualString('Technical Support', 'الدعم الفني'),
                'depth' => 1,
                'is_active' => true,
            ]
        );

        $billing = TicketCategory::firstOrCreate(
            ['code' => 'billing'],
            [
                'uuid' => Str::uuid(),
                'name' => new BilingualString('Billing', 'الفواتير'),
                'depth' => 1,
                'is_active' => true,
            ]
        );

        $software = TicketCategory::firstOrCreate(
            ['code' => 'support.software'],
            [
                'uuid' => Str::uuid(),
                'parent_id' => $support->id,
                'name' => new BilingualString('Software Issues', 'مشاكل البرمجيات'),
                'depth' => 2,
                'is_active' => true,
            ]
        );

        $software->fields()->firstOrCreate(
            ['key' => 'severity'],
            [
                'uuid' => Str::uuid(),
                'label' => new BilingualString('Severity Level', 'مستوى الخطورة'),
                'type' => 'select',
                'options' => ['low', 'medium', 'high', 'critical'],
                'is_required' => true,
            ]
        );

        $software->fields()->firstOrCreate(
            ['key' => 'error_code'],
            [
                'uuid' => Str::uuid(),
                'label' => new BilingualString('Error Code', 'رمز الخطأ'),
                'type' => 'text',
                'is_required' => false,
            ]
        );
    }
}
