<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsignacionesTestTable extends Migration
{
    public function up()
    {
        Schema::create('asignaciones_test', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->onDelete('cascade');
            $table->foreignId('profesor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('grupo_id')->constrained('grupos')->onDelete('cascade');
            $table->string('clave_acceso', 50)->unique();
            $table->enum('estado', ['pendiente', 'en progreso', 'aplicado']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('asignaciones_test');
    }
}
