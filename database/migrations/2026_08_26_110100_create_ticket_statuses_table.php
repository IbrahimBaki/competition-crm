<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_statuses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('key', 64)->unique();
            $table->json('name');
            $table->string('lifecycle_type', 24);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->index('lifecycle_type');
        });

        if (! Schema::hasTable('ticket_statuses') || DB::table('ticket_statuses')->count() === 0) {
            $this->seedDefaultStatuses();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_statuses');
    }

    private function seedDefaultStatuses(): void
    {
        $now = now();
        $statuses = [
            ['key' => 'new', 'lifecycle_type' => 'new', 'position' => 0, 'is_default' => true],
            ['key' => 'open', 'lifecycle_type' => 'open', 'position' => 1, 'is_default' => false],
            ['key' => 'pending', 'lifecycle_type' => 'pending', 'position' => 2, 'is_default' => false],
            ['key' => 'resolved', 'lifecycle_type' => 'resolved', 'position' => 3, 'is_default' => false],
            ['key' => 'closed', 'lifecycle_type' => 'closed', 'position' => 4, 'is_default' => false],
            ['key' => 'spam', 'lifecycle_type' => 'spam', 'position' => 5, 'is_default' => false],
        ];

        foreach ($statuses as $status) {
            DB::table('ticket_statuses')->insert([
                'uuid' => Str::uuid(),
                'key' => $status['key'],
                'name' => json_encode([
                    'en' => ucfirst($status['key']),
                    'ar' => match ($status['key']) {
                        'new' => 'جديد',
                        'open' => 'مفتوح',
                        'pending' => 'قيد الانتظار',
                        'resolved' => 'تم الحل',
                        'closed' => 'مغلق',
                        'spam' => 'بريد عشوائي',
                        default => ucfirst($status['key']),
                    },
                ]),
                'lifecycle_type' => $status['lifecycle_type'],
                'is_default' => $status['is_default'],
                'is_system' => true,
                'position' => $status['position'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
