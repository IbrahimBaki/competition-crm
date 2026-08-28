<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference', 32)->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->char('department_id', 36);
            $table->foreignId('ticket_category_id')->nullable()->constrained('ticket_categories')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject', 255);
            $table->string('subject_normalised', 255)->index();
            $table->text('body');
            $table->text('body_normalised')->nullable();
            $table->string('status', 24)->default('new')->index();
            $table->string('priority', 16)->index();
            $table->json('custom_fields')->nullable();
            $table->timestampsTz();

            $table->foreign('department_id')->references('id')->on('departments')->restrictOnDelete();
            $table->index(['status', 'priority']);
            $table->index(['department_id', 'status']);
            $table->index(['customer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
