<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_guest_grants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('token_hash')->unique();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->unsignedBigInteger('web_form_submission_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->foreign('ticket_id')
                ->references('id')
                ->on('tickets')
                ->nullOnDelete();

            $table->foreign('web_form_submission_id')
                ->references('id')
                ->on('web_form_submissions')
                ->nullOnDelete();

            $table->index(['token_hash', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_guest_grants');
    }
};
