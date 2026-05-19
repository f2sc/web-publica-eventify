<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->nullable()->constrained('articulos')->nullOnDelete();
            $table->string('provider', 30);
            $table->string('model', 100);
            $table->enum('type', ['full_article', 'image', 'field_regen']);
            $table->string('field_name', 50)->nullable();
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->decimal('cost_usd', 8, 6)->nullable();
            $table->enum('status', ['ok', 'error'])->default('ok');
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
    }
};
