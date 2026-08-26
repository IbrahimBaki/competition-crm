<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 255);
            $table->string('name_normalised', 255)->index();
            $table->string('service_tier', 32)->default('standard');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_accounts');
    }
};
