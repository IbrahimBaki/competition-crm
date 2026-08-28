<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quick_replies', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('scope'); // personal, shared
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->json('title'); // {ar, en}
            $table->json('body'); // {ar, en}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quick_replies');
    }
};
