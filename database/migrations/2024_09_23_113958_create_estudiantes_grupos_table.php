<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstudiantesGruposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estudiantes_grupos', function (Blueprint $table) {
            $table->id(); // Llave primaria autoincremental
            $table->foreignId('id_grupo')->constrained('grupos')->onDelete('cascade'); // Relación con la tabla grupos
            $table->foreignId('id_estudiante')->constrained('estudiantes')->onDelete('cascade'); // Relación con la tabla estudiantes
            $table->timestamps(); // Timestamps automáticos
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estudiantes_grupos');
    }
}
