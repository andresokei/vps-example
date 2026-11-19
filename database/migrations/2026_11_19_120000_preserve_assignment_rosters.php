<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('asignaciones_test', 'alumnado_ids')) {
            Schema::table('asignaciones_test', fn (Blueprint $t) => $t->json('alumnado_ids')->nullable());
        }
        DB::table('asignaciones_test')->whereNull('alumnado_ids')
            ->whereIn('id', DB::table('respuestas')->select('asignacion_test_id'))
            ->chunkById(100, function ($assignments) {
                foreach ($assignments as $assignment) {
                    $members = DB::table('estudiantes_grupos')->where('id_grupo', $assignment->grupo_id)->pluck('id_estudiante');
                    $answers = DB::table('respuestas')->where('asignacion_test_id', $assignment->id)->get(['alumno_id','respuesta','tipo_relacion']);
                    $known = $members->concat($answers->pluck('alumno_id'))
                        ->concat($answers->whereIn('tipo_relacion', ['preferencia','rechazo'])->pluck('respuesta')->filter(fn ($v) => ctype_digit((string)$v)));
                    $ids = DB::table('estudiantes')->whereIntegerInRaw('id', $known->unique()->all())->orderBy('id')->pluck('id');
                    DB::table('asignaciones_test')->where('id', $assignment->id)->whereNull('alumnado_ids')
                        ->update(['alumnado_ids' => $ids->toJson()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('asignaciones_test', fn (Blueprint $t) => $t->dropColumn('alumnado_ids'));
    }
};
