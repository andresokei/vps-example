<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteUserAccountService
{
    public function delete(User $user): void
    {
        $assignments = DB::table('asignaciones_test')
            ->where('profesor_id', $user->id)
            ->get(['id', 'grupo_id']);

        DB::transaction(function () use ($user) {
            $studentIds = DB::table('estudiantes_grupos')
                ->join('grupos', 'grupos.id', '=', 'estudiantes_grupos.id_grupo')
                ->where('grupos.id_profesor', $user->id)
                ->pluck('estudiantes_grupos.id_estudiante')
                ->unique();

            $user->delete();

            if ($studentIds->isNotEmpty()) {
                Estudiante::query()
                    ->whereIn('id', $studentIds)
                    ->whereDoesntHave('grupos')
                    ->delete();
            }
        });

        foreach ($assignments as $assignment) {
            app(AnalisisGrupalService::class)->invalidateForAssignment(
                (int) $assignment->grupo_id,
                (int) $assignment->id
            );
        }
    }
}
