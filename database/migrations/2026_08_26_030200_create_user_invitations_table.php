<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_invitations', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('email');
            $table->char('invited_by_uuid', 36);
            $table->foreign('invited_by_uuid')->references('uuid')->on('users');
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->char('accepted_user_uuid', 36)->nullable();
            $table->foreign('accepted_user_uuid')->references('uuid')->on('users');
            $table->timestamps();
            $table->index(['email', 'accepted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_invitations');
    }
};
