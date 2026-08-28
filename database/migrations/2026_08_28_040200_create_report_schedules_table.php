<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_schedules', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('name');
            $table->string('report_key');
            $table->string('format');
            $table->json('filters'); // relative range like 'last_7_days', resolved at runtime
            $table->string('frequency'); // daily, weekly, monthly
            $table->unsignedTinyInteger('day_of_week')->nullable(); // 0=Sunday, 1=Monday...
            $table->unsignedTinyInteger('day_of_month')->nullable(); // 1-31
            $table->time('run_at_time');
            $table->string('timezone');
            $table->json('recipients'); // user UUIDs and/or email addresses
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('last_run_at')->nullable();
            $table->dateTime('next_run_at');
            $table->timestamps();

            $table->index(['is_active', 'next_run_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_schedules');
    }
};
