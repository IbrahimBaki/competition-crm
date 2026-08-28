<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_branch', function (Blueprint $table) {
            $table->char('user_uuid', 36);
            $table->char('branch_id', 36);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->primary(['user_uuid', 'branch_id']);
            $table->foreign('user_uuid')->references('uuid')->on('users')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_branch');
    }
};
