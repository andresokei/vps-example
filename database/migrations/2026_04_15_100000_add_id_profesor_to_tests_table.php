<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->foreignId('id_profesor')->nullable()->after('descripcion')
                  ->constrained('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropForeign(['id_profesor']);
            $table->dropColumn('id_profesor');
        });
    }
};
