<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_message_delivery_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid()->unique();

            $table->foreignId('ticket_message_id')->constrained('ticket_messages')->cascadeOnDelete();

            $table->string('from_state', 16)->nullable();
            $table->string('to_state', 16)->notNullable();

            $table->string('reason', 64)->nullable();
            $table->text('detail')->nullable();

            $table->timestamp('occurred_at')->notNullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['ticket_message_id', 'occurred_at'], 'tmde_msg_occurred_idx');
        });
    }

    public function down(): void
    {
        // Dropped by ticket_messages migration to handle foreign key constraint
    }
};
