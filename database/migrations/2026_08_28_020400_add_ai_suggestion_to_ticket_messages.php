<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->foreignId('ai_suggestion_id')->nullable()->after('id')->constrained('ai_suggestions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ai_suggestion_id');
        });
    }
};
