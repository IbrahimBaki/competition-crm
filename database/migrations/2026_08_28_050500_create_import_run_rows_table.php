<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_run_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_run_id')->constrained('import_runs')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('state', 20); // valid, invalid, imported, skipped
            $table->json('errors')->nullable();
            $table->string('external_ref', 255)->nullable()->index();
            $table->string('created_entity_uuid', 36)->nullable();
            $table->timestampsTz();
            $table->unique(['import_run_id', 'row_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_run_rows');
    }
};
