<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('web_form_id')->constrained('web_forms');
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('state');
            $table->string('fingerprint')->index();
            $table->string('tracking_token')->unique()->index();
            $table->json('payload');
            $table->string('submitter_ip_hash')->nullable();
            $table->string('user_agent')->nullable();
            $table->foreignId('duplicate_of_id')->nullable()->constrained('web_form_submissions')->nullOnDelete();
            $table->timestamps();
            $table->index(['web_form_id', 'fingerprint', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_form_submissions');
    }
};
