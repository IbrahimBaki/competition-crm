<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_department', function (Blueprint $table) {
            $table->char('user_uuid', 36);
            $table->char('department_id', 36);
            $table->timestamps();

            $table->primary(['user_uuid', 'department_id']);
            $table->foreign('user_uuid')->references('uuid')->on('users')->cascadeOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_department');
    }
};
