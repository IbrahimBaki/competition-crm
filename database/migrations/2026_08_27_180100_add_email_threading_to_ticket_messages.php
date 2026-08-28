<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->string('in_reply_to', 191)->nullable()->after('external_message_id');
            $table->text('reference_ids')->nullable()->after('in_reply_to');   // JSON list of RFC References
            $table->string('raw_source_path', 512)->nullable()->after('reference_ids');
            $table->string('inbound_classification', 32)->nullable()->after('raw_source_path');
            $table->index('in_reply_to');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->dropIndex(['in_reply_to']);
            $table->dropColumn(['in_reply_to', 'reference_ids', 'raw_source_path', 'inbound_classification']);
        });
    }
};
