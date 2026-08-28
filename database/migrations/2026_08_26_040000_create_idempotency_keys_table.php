<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key', 191);
            $table->string('scope', 191);
            $table->string('request_fingerprint', 64);
            $table->unsignedSmallInteger('status_code');
            $table->json('response_body');
            $table->json('response_headers');
            $table->timestampTz('created_at');
            $table->timestampTz('expires_at');

            $table->unique(['scope', 'key']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
