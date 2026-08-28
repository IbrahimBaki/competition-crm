<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_form_fields', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('web_form_id')->constrained('web_forms')->cascadeOnDelete();
            $table->string('key');
            $table->string('type');
            $table->boolean('is_required')->default(false);
            $table->json('label');
            $table->json('options')->nullable();
            $table->json('validation')->nullable();
            $table->string('maps_to')->nullable();
            $table->unsignedInteger('position');
            $table->timestamps();
            $table->unique(['web_form_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_form_fields');
    }
};
