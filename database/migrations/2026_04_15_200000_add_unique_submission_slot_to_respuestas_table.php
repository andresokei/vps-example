<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('respuestas')
            ->select(
                'asignacion_test_id',
                'alumno_id',
                'pregunta_id',
                'orden_preferencia',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('asignacion_test_id', 'alumno_id', 'pregunta_id', 'orden_preferencia')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            $sample = $duplicates
                ->take(5)
                ->map(fn ($row) => sprintf(
                    '[asignacion=%s alumno=%s pregunta=%s orden=%s total=%s]',
                    $row->asignacion_test_id,
                    $row->alumno_id,
                    $row->pregunta_id,
                    $row->orden_preferencia,
                    $row->total
                ))
                ->implode(' ');

            throw new \RuntimeException(
                'Cannot add respuestas_unique_submission_slot because duplicate answer slots already exist. '
                .'No rows were deleted. Review and clean duplicates first. Sample: '.$sample
            );
        }

        Schema::table('respuestas', function (Blueprint $table) {
            $table->unique(
                ['asignacion_test_id', 'alumno_id', 'pregunta_id', 'orden_preferencia'],
                'respuestas_unique_submission_slot'
            );
        });
    }

    public function down(): void
    {
        Schema::table('respuestas', function (Blueprint $table) {
            $table->dropUnique('respuestas_unique_submission_slot');
        });
    }
};
