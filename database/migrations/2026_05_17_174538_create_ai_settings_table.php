<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            // Proveedor de texto
            $table->string('text_provider', 30)->default('claude');
            $table->string('text_model', 100)->default('claude-sonnet-4-6');
            $table->text('text_api_key')->nullable();
            // Proveedor de imagen
            $table->string('image_provider', 30)->default('google');
            $table->string('image_model', 100)->default('imagen-4.0-flash-exp');
            $table->text('image_api_key')->nullable();
            $table->string('image_size', 20)->default('1024x1024');
            $table->string('image_style', 50)->nullable();
            // Prompts editables
            $table->longText('prompt_system')->nullable();
            $table->longText('prompt_image')->nullable();
            $table->longText('prompt_interlinking')->nullable();
            // Parámetros globales
            $table->string('default_tone', 100)->default('profesional y cercano');
            $table->string('default_language', 10)->default('es');
            $table->string('default_length', 50)->default('1000-1500 palabras');
            $table->unsignedTinyInteger('max_articles_context')->default(5);
            $table->unsignedTinyInteger('max_internal_links')->default(2);
            $table->boolean('auto_generate_image')->default(false);
            $table->boolean('auto_generate_faq')->default(true);
            $table->boolean('always_draft')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
