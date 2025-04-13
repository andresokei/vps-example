<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Respuesta;
use App\Models\AsignacionTest;
use App\Models\User;

class SociometricSeeder extends Seeder
{
    /**
     * Seed sociometric test data for a more comprehensive analysis.
     *
     * @return void
     */
    public function run()
    {
        // Seleccionar grupo para el que generaremos datos (asumimos que ya existe)
        $grupo = Grupo::where('nombre_grupo', 'grupo1')->first();
        
        if (!$grupo) {
            $this->command->error('El grupo "grupo1" no existe. Por favor, créalo primero.');
            return;
        }
        
        // Seleccionar la asignación de test para este grupo (asumimos que ya existe)
        $asignacion = AsignacionTest::where('grupo_id', $grupo->id)->first();
        
        if (!$asignacion) {
            $this->command->error('No hay una asignación de test para el grupo seleccionado.');
            return;
        }
        
        // Obtener todas las preguntas de tipo preferencia y rechazo para esta asignación
        $preguntasPreferencia = DB::table('preguntas')
            ->where('test_id', $asignacion->test_id)
            ->where('tipo_pregunta', 'preferencia')
            ->get();
            
        $preguntasRechazo = DB::table('preguntas')
            ->where('test_id', $asignacion->test_id)
            ->where('tipo_pregunta', 'rechazo')
            ->get();
            
        if ($preguntasPreferencia->isEmpty() || $preguntasRechazo->isEmpty()) {
            $this->command->error('No hay preguntas suficientes para generar respuestas.');
            return;
        }
        
        // Obtener todos los estudiantes de este grupo
        $estudiantes = DB::table('estudiantes_grupos')
            ->join('estudiantes', 'estudiantes_grupos.id_estudiante', '=', 'estudiantes.id')
            ->where('estudiantes_grupos.id_grupo', $grupo->id)
            ->select('estudiantes.*')
            ->get();
            
        if ($estudiantes->count() < 4) {
            $this->command->error('No hay suficientes estudiantes en el grupo (mínimo 4 necesarios).');
            return;
        }
        
        // Limpiar respuestas anteriores para esta asignación
        Respuesta::where('asignacion_test_id', $asignacion->id)->delete();
        
        $this->command->info('Generando respuestas para el test sociométrico...');
        
        // Generar respuestas para cada estudiante
        foreach ($estudiantes as $estudiante) {
            // Obtener los demás estudiantes del grupo (posibles elecciones)
            $otrosEstudiantes = $estudiantes->where('id', '!=', $estudiante->id);
            
            // Para cada pregunta de preferencia, elegir aleatoriamente a otros estudiantes
            foreach ($preguntasPreferencia as $index => $pregunta) {
                // Seleccionar 3 estudiantes aleatorios para preferencia (o menos si no hay suficientes)
                $elegidos = $otrosEstudiantes->random(min(3, $otrosEstudiantes->count()))->shuffle();
                
                // Crear respuestas con orden de preferencia
                foreach ($elegidos as $posicion => $elegido) {
                    Respuesta::create([
                        'alumno_id' => $estudiante->id,
                        'asignacion_test_id' => $asignacion->id,
                        'pregunta_id' => $pregunta->id,
                        'respuesta' => $elegido->id,
                        'orden_preferencia' => $posicion + 1,
                        'tipo_relacion' => 'preferencia',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            // Para cada pregunta de rechazo, elegir aleatoriamente a otros estudiantes
            foreach ($preguntasRechazo as $index => $pregunta) {
                // Seleccionar 2 estudiantes aleatorios para rechazo (o menos si no hay suficientes)
                $elegidos = $otrosEstudiantes->random(min(2, $otrosEstudiantes->count()))->shuffle();
                
                // Crear respuestas con orden de rechazo
                foreach ($elegidos as $posicion => $elegido) {
                    Respuesta::create([
                        'alumno_id' => $estudiante->id,
                        'asignacion_test_id' => $asignacion->id,
                        'pregunta_id' => $pregunta->id,
                        'respuesta' => $elegido->id,
                        'orden_preferencia' => $posicion + 1,
                        'tipo_relacion' => 'rechazo',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        $this->command->info('✅ Respuestas generadas correctamente.');
        $this->command->info('⚠️ Recuerda ejecutar el análisis para generar las relaciones a partir de estas respuestas.');
    }
}