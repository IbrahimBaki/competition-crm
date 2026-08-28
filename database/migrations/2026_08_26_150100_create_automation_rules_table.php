<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('key')->unique();
            $table->json('name');
            $table->string('trigger');
            $table->string('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->unsignedSmallInteger('priority')->default(100);
            $table->boolean('stop_on_match')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('conditions');
            $table->json('actions');
            $table->unsignedSmallInteger('escalation_level')->nullable();
            $table->unsignedInteger('cooldown_minutes')->nullable();
            $table->timestamps();

            $table->index(['trigger', 'is_active', 'priority', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};
