<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            // nullable: artículos existentes sin categoría formal siguen funcionando
            $table->unsignedBigInteger('categoria_blog_id')->nullable()->after('imagen_principal');
            $table->foreign('categoria_blog_id')
                  ->references('id')->on('categorias_blog')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->dropForeign(['categoria_blog_id']);
            $table->dropColumn('categoria_blog_id');
        });
    }
};
