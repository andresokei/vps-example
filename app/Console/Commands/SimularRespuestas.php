<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Relacion;
use Illuminate\Support\Facades\DB;

class SimularRespuestas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simular:respuestas {grupo_id} {asignacion_test_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simula respuestas de estudiantes para un test en un grupo.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $grupo_id = $this->argument('grupo_id');
        $asignacion_test_id = $this->argument('asignacion_test_id');

        // Ejecutar la simulación de respuestas
        $this->generarRespuestasSimuladas($grupo_id, $asignacion_test_id);

        $this->info('Respuestas simuladas generadas para el test y grupo.');
        return 0;
    }

    function generarRespuestasSimuladas($grupo_id, $asignacion_test_id) {
        // Obtener todos los estudiantes del grupo
        $estudiantes = DB::table('estudiantes_grupos')
                        ->where('id_grupo', $grupo_id)
                        ->pluck('id_estudiante')
                        ->toArray();

        // Definir las preguntas de preferencia y rechazo
        $tiposRelaciones = [
            'preferido',
            'preferido',
            'rechazado',
            'rechazado'
        ];

        // Para cada estudiante, simular las respuestas
        foreach ($estudiantes as $alumno_a_id) {
            $companeros = array_diff($estudiantes, [$alumno_a_id]);
            shuffle($companeros);

            for ($i = 0; $i < 4; $i++) {
                $seleccionados = array_slice($companeros, $i * 3, 3);

                foreach ($seleccionados as $alumno_b_id) {
                    Relacion::create([
                        'asignacion_test_id' => $asignacion_test_id,
                        'alumno_a_id' => $alumno_a_id,
                        'alumno_b_id' => $alumno_b_id,
                        'tipo_relacion' => $tiposRelaciones[$i],
                        'intensidad' => rand(1, 3),
                        'estado_relacion' => 'activa',
                    ]);
                }
            }
        }
    }
}
