<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('event_id');
            $table->string('event_type');
            $table->foreignId('recipient_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('channel');
            $table->string('state');
            $table->string('locale', 5);
            $table->json('payload');
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('failure_code')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'recipient_user_id', 'channel']);
            $table->index(['recipient_user_id', 'state', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
