<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('report_key');
            $table->string('format'); // csv, xlsx, pdf
            $table->json('filters');
            $table->foreignId('requested_by_user_id')->constrained('users')->onDelete('cascade');
            $table->string('state'); // pending, running, ready, failed
            $table->unsignedInteger('row_count')->nullable();
            $table->foreignId('attachment_id')->nullable()->constrained('attachments')->onDelete('set null');
            $table->string('failure_code')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();

            $table->index(['requested_by_user_id', 'created_at']);
            $table->index(['state', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
