<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->string('focus_keyword', 150)->nullable()->after('etiquetas');
            $table->string('image_alt', 255)->nullable()->after('imagen_principal');
            $table->text('faq_json')->nullable()->after('schema_type');
            $table->text('ai_context_summary')->nullable()->after('faq_json');
            $table->string('summary_short', 255)->nullable()->after('ai_context_summary');
            $table->boolean('ai_generated')->default(false)->after('summary_short');
            $table->string('ai_last_provider', 50)->nullable()->after('ai_generated');
            $table->string('ai_last_model', 100)->nullable()->after('ai_last_provider');
            $table->timestamp('ai_last_generated_at')->nullable()->after('ai_last_model');
        });
    }

    public function down(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->dropColumn([
                'focus_keyword', 'image_alt', 'faq_json',
                'ai_context_summary', 'summary_short', 'ai_generated',
                'ai_last_provider', 'ai_last_model', 'ai_last_generated_at',
            ]);
        });
    }
};
