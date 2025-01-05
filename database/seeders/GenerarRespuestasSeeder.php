<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Test;
use App\Models\Pregunta;
use App\Models\AsignacionTest;
use App\Models\Respuesta;
use App\Models\Relacion;
use Illuminate\Support\Facades\DB;

class GenerarRespuestasSeeder extends Seeder
{
    public function run()
    {
        // IDs existentes
        $grupoId = 1;
        $asignacionTestId = 1;
        $testId = 1;

        // Obtener estudiantes del grupo
        $estudiantes = Estudiante::whereIn('id', function ($query) use ($grupoId) {
            $query->select('id_estudiante')
                ->from('estudiantes_grupos')
                ->where('id_grupo', $grupoId);
        })->get();

        // Obtener preguntas del test
        $preguntas = Pregunta::where('test_id', $testId)->get();

        // Iterar sobre cada estudiante
        foreach ($estudiantes as $estudiante) {
            // Para cada pregunta
            foreach ($preguntas as $pregunta) {
                // Si la pregunta es de tipo 'preferencia' o 'rechazo'
                if (in_array($pregunta->tipo_pregunta, ['preferencia', 'rechazo'])) {
                    // Obtener IDs de otros estudiantes
                    $otrosEstudiantesIds = $estudiantes->where('id', '!=', $estudiante->id)->pluck('id')->toArray();

                    // Seleccionar aleatoriamente 3 estudiantes
                    shuffle($otrosEstudiantesIds);
                    $seleccionados = array_slice($otrosEstudiantesIds, 0, 3);

                    $orden = 1;
                    foreach ($seleccionados as $idSeleccionado) {
                        // Crear respuesta
                        Respuesta::create([
                            'alumno_id' => $estudiante->id,
                            'asignacion_test_id' => $asignacionTestId,
                            'pregunta_id' => $pregunta->id,
                            'respuesta' => $idSeleccionado,
                            'orden_preferencia' => $orden,
                            'tipo_relacion' => $pregunta->tipo_pregunta,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Crear relación
                        Relacion::create([
                            'asignacion_test_id' => $asignacionTestId,
                            'alumno_a_id' => $estudiante->id,
                            'alumno_b_id' => $idSeleccionado,
                            'tipo_relacion' => $pregunta->tipo_pregunta == 'preferencia' ? 'preferido' : 'rechazado',
                            'intensidad' => $orden,
                            'estado_relacion' => 'activa',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $orden++;
                    }
                } else {
                    // Si es otro tipo de pregunta (por ejemplo, texto)
                    Respuesta::create([
                        'alumno_id' => $estudiante->id,
                        'asignacion_test_id' => $asignacionTestId,
                        'pregunta_id' => $pregunta->id,
                        'respuesta' => 'Respuesta de ejemplo', // Puedes personalizar esto
                        'orden_preferencia' => null,
                        'tipo_relacion' => 'texto',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
