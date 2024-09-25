<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelacionesTable extends Migration
{
    public function up()
    {
        Schema::create('relaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_test_id')->constrained('asignaciones_test')->onDelete('cascade');
            $table->foreignId('alumno_a_id')->constrained('estudiantes')->onDelete('cascade');
            $table->foreignId('alumno_b_id')->constrained('estudiantes')->onDelete('cascade');
            $table->enum('tipo_relacion', ['preferido', 'rechazado']);
            $table->integer('intensidad')->nullable();
            $table->enum('estado_relacion', ['activa', 'inactiva']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('relaciones');
    }
}
