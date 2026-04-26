<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articulos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->text('extracto')->nullable();
            $table->longText('contenido')->nullable();
            $table->string('imagen_principal')->nullable();
            $table->string('categoria_blog')->nullable();
            $table->string('etiquetas')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical')->nullable();
            $table->boolean('indexable')->default(true);
            $table->string('og_image')->nullable();
            $table->string('schema_type')->default('BlogPosting');
            $table->string('autor')->nullable();
            $table->enum('estado', ['borrador', 'publicado', 'archivado'])->default('borrador');
            $table->timestamp('fecha_publicacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articulos');
    }
};
