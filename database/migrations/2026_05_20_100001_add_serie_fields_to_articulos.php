<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->foreignId('serie_id')
                  ->nullable()->after('ai_last_generated_at')
                  ->constrained('series')->nullOnDelete();
            $table->unsignedSmallInteger('orden_en_serie')->nullable()->after('serie_id');
            $table->boolean('enviar_newsletter')->default(true)->after('orden_en_serie');
        });

        // MySQL ENUM modification — Blueprint cannot do this cleanly
        DB::statement("ALTER TABLE articulos MODIFY COLUMN estado ENUM('borrador','programado','publicado','archivado') DEFAULT 'borrador'");
    }

    public function down(): void
    {
        // Will fail if any row has estado='programado' — expected
        DB::statement("ALTER TABLE articulos MODIFY COLUMN estado ENUM('borrador','publicado','archivado') DEFAULT 'borrador'");

        Schema::table('articulos', function (Blueprint $table) {
            $table->dropForeign(['serie_id']);
            $table->dropColumn(['serie_id', 'orden_en_serie', 'enviar_newsletter']);
        });
    }
};
