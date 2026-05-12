<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preguntas', function (Blueprint $table) {
            $table->boolean('permite_respuesta_vacia')->default(false)->after('tipo_pregunta');
        });

        Schema::table('respuestas', function (Blueprint $table) {
            $table->string('respuesta')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('respuestas')->whereNull('respuesta')->update(['respuesta' => '']);

        Schema::table('respuestas', function (Blueprint $table) {
            $table->string('respuesta')->nullable(false)->change();
        });

        Schema::table('preguntas', function (Blueprint $table) {
            $table->dropColumn('permite_respuesta_vacia');
        });
    }
};
