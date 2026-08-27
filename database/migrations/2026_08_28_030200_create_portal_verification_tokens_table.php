<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_verification_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('portal_account_id');
            $table->string('contact_channel'); // 'email' or 'phone'
            $table->string('contact_value_hash')->index();
            $table->string('token_hash')->unique();
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->foreign('portal_account_id')
                ->references('id')
                ->on('portal_accounts')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_verification_tokens');
    }
};
