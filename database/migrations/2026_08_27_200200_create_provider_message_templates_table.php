<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_message_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid()->unique();

            $table->string('channel', 20)->notNullable();
            $table->string('key', 100)->notNullable();
            $table->string('provider_template_name', 255)->nullable();

            $table->text('body_en')->notNullable();
            $table->text('body_ar')->notNullable();

            $table->json('variables')->notNullable();

            $table->boolean('is_active')->default(true)->notNullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->unique(['channel', 'key']);
            $table->index(['channel', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_message_templates');
    }
};
