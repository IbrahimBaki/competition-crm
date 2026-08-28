<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->decimal('ai_classification_confidence', 4, 3)->nullable()->after('ticket_category_id');
            $table->timestamp('ai_classified_at')->nullable()->after('ai_classification_confidence');
            $table->string('classification_source')->nullable()->after('ai_classified_at');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('ai_classification_confidence');
            $table->dropColumn('ai_classified_at');
            $table->dropColumn('classification_source');
        });
    }
};
