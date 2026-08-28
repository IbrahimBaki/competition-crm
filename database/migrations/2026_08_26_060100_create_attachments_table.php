<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->string('disk');
            $table->string('storage_key')->unique();
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum_sha256', 64)->nullable();
            $table->string('scan_state')->default('pending');
            $table->string('scan_reason')->nullable();
            $table->timestampTz('scanned_at')->nullable();
            $table->nullableMorphs('attachable');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestampsTz();

            $table->index('scan_state');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
