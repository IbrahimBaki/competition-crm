<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_account_id')->nullable()->constrained('company_accounts')->nullOnDelete();
            $table->string('name', 255);
            $table->string('name_normalised', 255)->index();
            $table->string('preferred_locale', 8)->default('en');
            $table->string('status', 16)->default('active')->index();
            $table->text('blocked_reason')->nullable();
            $table->timestampTz('blocked_at')->nullable();
            $table->foreignId('blocked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('anonymised_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
