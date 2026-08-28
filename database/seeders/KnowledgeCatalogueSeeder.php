<?php

namespace Database\Seeders;

use App\Domains\Knowledge\Models\KnowledgeCategory;
use App\Support\I18n\BilingualString;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class KnowledgeCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('knowledge_categories')) {
            $this->command?->warn('Knowledge catalogue tables not yet migrated; skipping knowledge catalogues.');

            return;
        }

        $gettingStarted = KnowledgeCategory::firstOrCreate(
            ['code' => 'getting-started'],
            [
                'uuid' => Str::uuid(),
                'name' => new BilingualString('Getting Started', 'البدء السريع'),
                'depth' => 0,
                'position' => 1,
                'is_active' => true,
            ]
        );

        $faq = KnowledgeCategory::firstOrCreate(
            ['code' => 'faq'],
            [
                'uuid' => Str::uuid(),
                'name' => new BilingualString('Frequently Asked Questions', 'الأسئلة الشائعة'),
                'depth' => 0,
                'position' => 2,
                'is_active' => true,
            ]
        );

        $troubleshooting = KnowledgeCategory::firstOrCreate(
            ['code' => 'troubleshooting'],
            [
                'uuid' => Str::uuid(),
                'name' => new BilingualString('Troubleshooting', 'استكشاف الأخطاء'),
                'depth' => 0,
                'position' => 3,
                'is_active' => true,
            ]
        );

        KnowledgeCategory::firstOrCreate(
            ['code' => 'troubleshooting.authentication'],
            [
                'uuid' => Str::uuid(),
                'parent_id' => $troubleshooting->id,
                'name' => new BilingualString('Authentication Issues', 'مشاكل المصادقة'),
                'depth' => 1,
                'position' => 1,
                'is_active' => true,
            ]
        );

        KnowledgeCategory::firstOrCreate(
            ['code' => 'troubleshooting.integration'],
            [
                'uuid' => Str::uuid(),
                'parent_id' => $troubleshooting->id,
                'name' => new BilingualString('Integration Problems', 'مشاكل التكامل'),
                'depth' => 1,
                'position' => 2,
                'is_active' => true,
            ]
        );
    }
}
