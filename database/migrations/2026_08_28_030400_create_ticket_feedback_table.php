<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_feedback', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedTinyInteger('score');
            $table->text('comment')->nullable();
            $table->timestamp('submitted_at');
            $table->string('source'); // 'portal' or 'link'
            $table->timestamps();

            $table->foreign('ticket_id')
                ->references('id')
                ->on('tickets')
                ->cascadeOnDelete();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->nullOnDelete();

            $table->unique('ticket_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_feedback');
    }
};
