<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_loop_guards', function (Blueprint $table) {
            $table->id();
            $table->string('sender_hash', 64);
            $table->timestamp('window_started_at');
            $table->unsignedInteger('hits')->default(1);
            $table->boolean('tripped')->default(false);
            $table->timestamps();
            $table->unique('sender_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_loop_guards');
    }
};
