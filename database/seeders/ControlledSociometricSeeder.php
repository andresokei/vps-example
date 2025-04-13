<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Respuesta;
use App\Models\AsignacionTest;

class RobustSociometricSeeder extends Seeder
{
    /**
     * Seed sociometric data with specific patterns.
     *
     * @return void
     */
    public function run()
    {
        // Seleccionar grupo para el que generaremos datos
        $grupo = Grupo::where('nombre_grupo', 'grupo1')->first();
        
        if (!$grupo) {
            $this->command->error('El grupo "grupo1" no existe. Por favor, créalo primero.');
            return;
        }
        
        // Seleccionar la asignación de test para este grupo
        $asignacion = AsignacionTest::where('grupo_id', $grupo->id)->first();
        
        if (!$asignacion) {
            $this->command->error('No hay una asignación de test para el grupo seleccionado.');
            return;
        }
        
        // Obtener todas las preguntas para esta asignación
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
        
        // Obtener estudiantes del grupo como array
        $estudiantes = DB::table('estudiantes_grupos')
            ->join('estudiantes', 'estudiantes_grupos.id_estudiante', '=', 'estudiantes.id')
            ->where('estudiantes_grupos.id_grupo', $grupo->id)
            ->select('estudiantes.*')
            ->get()
            ->toArray();
            
        if (count($estudiantes) < 4) {
            $this->command->error('Este seeder requiere al menos 4 estudiantes en el grupo.');
            return;
        }
        
        // Limpiar respuestas anteriores para esta asignación
        Respuesta::where('asignacion_test_id', $asignacion->id)->delete();
        
        // Mostrar la información de los estudiantes para depuración
        $this->command->info('Estudiantes en el grupo:');
        foreach ($estudiantes as $index => $est) {
            $this->command->info("[$index] ID: {$est->id}, Nombre: {$est->nombre}");
        }
        
        // Asignar roles específicos para patrones de relación
        $estudiante_popular = $estudiantes[0]; // Estudiante que todos prefieren
        $estudiante_rechazado = $estudiantes[1]; // Estudiante que todos rechazan
        $estudiante_lider = $estudiantes[2]; // Estudiante con múltiples conexiones
        $estudiante_aislado = $estudiantes[3]; // Estudiante con pocas conexiones
        $otros_estudiantes = array_slice($estudiantes, 4); // Resto de estudiantes
        
        $this->command->info('Generando patrones de relación específicos...');
        $this->command->info("- Estudiante popular: {$estudiante_popular->nombre} (ID: {$estudiante_popular->id})");
        $this->command->info("- Estudiante rechazado: {$estudiante_rechazado->nombre} (ID: {$estudiante_rechazado->id})");
        $this->command->info("- Estudiante líder: {$estudiante_lider->nombre} (ID: {$estudiante_lider->id})");
        $this->command->info("- Estudiante aislado: {$estudiante_aislado->nombre} (ID: {$estudiante_aislado->id})");
        
        // 1. Crear un patrón donde el estudiante popular es preferido por casi todos
        foreach ($estudiantes as $estudiante) {
            if ($estudiante->id != $estudiante_popular->id) {
                foreach ($preguntasPreferencia as $pregunta) {
                    // Todos prefieren al estudiante popular en primer lugar
                    Respuesta::create([
                        'alumno_id' => $estudiante->id,
                        'asignacion_test_id' => $asignacion->id,
                        'pregunta_id' => $pregunta->id,
                        'respuesta' => $estudiante_popular->id,
                        'orden_preferencia' => 1,
                        'tipo_relacion' => 'preferencia',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    // Segunda preferencia hacia el líder (excepto si es él mismo)
                    if ($estudiante->id != $estudiante_lider->id) {
                        Respuesta::create([
                            'alumno_id' => $estudiante->id,
                            'asignacion_test_id' => $asignacion->id,
                            'pregunta_id' => $pregunta->id,
                            'respuesta' => $estudiante_lider->id,
                            'orden_preferencia' => 2,
                            'tipo_relacion' => 'preferencia',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
        
        // 2. Crear un patrón donde el estudiante rechazado es rechazado por casi todos
        foreach ($estudiantes as $estudiante) {
            if ($estudiante->id != $estudiante_rechazado->id) {
                foreach ($preguntasRechazo as $pregunta) {
                    // Todos rechazan al estudiante rechazado en primer lugar
                    Respuesta::create([
                        'alumno_id' => $estudiante->id,
                        'asignacion_test_id' => $asignacion->id,
                        'pregunta_id' => $pregunta->id,
                        'respuesta' => $estudiante_rechazado->id,
                        'orden_preferencia' => 1,
                        'tipo_relacion' => 'rechazo',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        // 3. Crear relaciones para el estudiante líder (múltiples conexiones)
        // El líder prefiere a algunos otros estudiantes
        if (!empty($otros_estudiantes)) {
            $max_conexiones = min(3, count($otros_estudiantes));
            for ($i = 0; $i < $max_conexiones; $i++) {
                foreach ($preguntasPreferencia as $pregunta) {
                    Respuesta::create([
                        'alumno_id' => $estudiante_lider->id,
                        'asignacion_test_id' => $asignacion->id,
                        'pregunta_id' => $pregunta->id,
                        'respuesta' => $otros_estudiantes[$i]->id,
                        'orden_preferencia' => $i + 1,
                        'tipo_relacion' => 'preferencia',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        } else {
            // Si no hay otros estudiantes, el líder prefiere al popular
            foreach ($preguntasPreferencia as $pregunta) {
                Respuesta::create([
                    'alumno_id' => $estudiante_lider->id,
                    'asignacion_test_id' => $asignacion->id,
                    'pregunta_id' => $pregunta->id,
                    'respuesta' => $estudiante_popular->id,
                    'orden_preferencia' => 1,
                    'tipo_relacion' => 'preferencia',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        // 4. El estudiante popular prefiere al líder
        foreach ($preguntasPreferencia as $pregunta) {
            Respuesta::create([
                'alumno_id' => $estudiante_popular->id,
                'asignacion_test_id' => $asignacion->id,
                'pregunta_id' => $pregunta->id,
                'respuesta' => $estudiante_lider->id,
                'orden_preferencia' => 1,
                'tipo_relacion' => 'preferencia',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // 5. El estudiante aislado no recibe preferencias específicas pero emite algunas
        foreach ($preguntasPreferencia as $pregunta) {
            // El aislado prefiere al popular
            Respuesta::create([
                'alumno_id' => $estudiante_aislado->id,
                'asignacion_test_id' => $asignacion->id,
                'pregunta_id' => $pregunta->id,
                'respuesta' => $estudiante_popular->id,
                'orden_preferencia' => 1,
                'tipo_relacion' => 'preferencia',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // 6. Algunos rechazos adicionales hacia el estudiante aislado
        foreach ($estudiantes as $estudiante) {
            if ($estudiante->id != $estudiante_aislado->id && $estudiante->id != $estudiante_rechazado->id) {
                foreach ($preguntasRechazo as $index => $pregunta) {
                    if ($index > 0) { // Para la segunda pregunta de rechazo
                        Respuesta::create([
                            'alumno_id' => $estudiante->id,
                            'asignacion_test_id' => $asignacion->id,
                            'pregunta_id' => $pregunta->id,
                            'respuesta' => $estudiante_aislado->id,
                            'orden_preferencia' => 2,
                            'tipo_relacion' => 'rechazo',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
        
        // 7. Generar algunas conexiones adicionales entre otros estudiantes si hay más de 4
        if (!empty($otros_estudiantes)) {
            foreach ($otros_estudiantes as $index => $estudiante) {
                // Encuentra a otro estudiante al que preferir (que no sea él mismo)
                $destino_index = ($index + 1) % count($otros_estudiantes);
                $destino = $otros_estudiantes[$destino_index];
                
                foreach ($preguntasPreferencia as $pregunta) {
                    if ($estudiante->id != $destino->id) {
                        Respuesta::create([
                            'alumno_id' => $estudiante->id,
                            'asignacion_test_id' => $asignacion->id,
                            'pregunta_id' => $pregunta->id,
                            'respuesta' => $destino->id,
                            'orden_preferencia' => 3, // Tercera preferencia después del popular y el líder
                            'tipo_relacion' => 'preferencia',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
        
        $this->command->info('✅ Respuestas con patrones específicos generadas correctamente.');
        $this->command->info('⚠️ Recuerda ejecutar el análisis para generar las relaciones a partir de estas respuestas.');
    }
}