<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('relaciones', function (Blueprint $table) {
            // ⬇️ 1) nueva columna (nullable para no romper datos existentes)
            $table->unsignedBigInteger('pregunta_id')
                  ->nullable()
                  ->after('asignacion_test_id');

            // ⬇️ 2) índice + clave foránea opcional
            $table->foreign('pregunta_id')
                  ->references('id')
                  ->on('preguntas')
                  ->cascadeOnDelete();   // o ->restrictOnDelete()
        });
    }

    public function down(): void
    {
        Schema::table('relaciones', function (Blueprint $table) {
            $table->dropForeign(['pregunta_id']);
            $table->dropColumn('pregunta_id');
        });
    }
};
