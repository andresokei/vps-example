<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropForeign(['id_profesor']); // Si existe la clave foránea
            $table->dropColumn('id_profesor');
        });
    }
    
    public function down()
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->foreignId('id_profesor')->constrained('users')->onDelete('cascade');
        });
    }
    
};
