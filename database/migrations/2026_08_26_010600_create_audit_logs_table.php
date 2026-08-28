<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('actor_uuid', 36)->nullable();
            $table->string('action', 128);
            $table->string('target_type', 64);
            $table->char('target_id', 36);
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->timestamp('recorded_at')->useCurrent();

            $table->index(['target_type', 'target_id']);
            $table->index(['actor_uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
