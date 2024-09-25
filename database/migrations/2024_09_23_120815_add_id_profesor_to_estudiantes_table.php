<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdProfesorToEstudiantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            if (!Schema::hasColumn('estudiantes', 'id_profesor')) {
                $table->foreignId('id_profesor')->constrained('users')->onDelete('cascade');
            }
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            // Verifica si la columna 'id_profesor' existe antes de intentar eliminarla
            if (Schema::hasColumn('estudiantes', 'id_profesor')) {
                // Si la clave foránea existe, intenta eliminarla
                try {
                    $table->dropForeign(['id_profesor']);
                } catch (\Illuminate\Database\QueryException $e) {
                    // Si la clave foránea no existe, no hacer nada
                    // Solo continúa con la eliminación de la columna
                }
                // Elimina la columna 'id_profesor'
                $table->dropColumn('id_profesor');
            }
        });
    }
    

}
