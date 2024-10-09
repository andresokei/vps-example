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
    Schema::table('respuestas', function (Blueprint $table) {
        $table->enum('tipo_relacion', ['preferencia', 'rechazo', 'texto'])->nullable()->after('orden_preferencia');
    });
}

public function down()
{
    Schema::table('respuestas', function (Blueprint $table) {
        $table->dropColumn('tipo_relacion');
    });
}


   
};
