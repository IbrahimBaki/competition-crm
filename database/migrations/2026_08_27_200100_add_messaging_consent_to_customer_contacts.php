<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            $table->timestamp('whatsapp_opted_in_at')->nullable()->after('value');
            $table->string('whatsapp_opt_in_source', 64)->nullable()->after('whatsapp_opted_in_at');
            $table->timestamp('sms_opted_out_at')->nullable()->after('whatsapp_opt_in_source');
            $table->string('sms_opt_out_source', 64)->nullable()->after('sms_opted_out_at');
        });
    }

    public function down(): void
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            $table->dropColumn('whatsapp_opted_in_at');
            $table->dropColumn('whatsapp_opt_in_source');
            $table->dropColumn('sms_opted_out_at');
            $table->dropColumn('sms_opt_out_source');
        });
    }
};
