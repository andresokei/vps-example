<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('estudiantes_grupos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_grupo')->constrained('grupos')->onDelete('cascade');
            $table->foreignId('id_estudiante')->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes_grupos');
    }
};
