<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Grupo;
use App\Models\AsignacionTest;
use App\Models\Relacion;
use App\Models\Estudiante;
use App\Models\Respuesta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalisisSelector extends Component
{
    public $grupos;
    public $grupoSeleccionado;
    public $tipoAnalisis;
    public $resultadoAnalisis;
    public $asignacionTestId;
    public $jsonData = null;
    
    // Para el recuento de preferencias
    public $labels = [];
    public $data = [];

    public function mount()
    {
        $this->grupos = Grupo::where('id_profesor', Auth::user()->id)->get();
        
        // Inicializar tipo de análisis
        $this->tipoAnalisis = '';
    }

    public function updatedGrupoSeleccionado()
    {
        // Cuando cambia el grupo, buscamos asignaciones
        if ($this->grupoSeleccionado) {
            Log::info("Buscando asignaciones para el grupo {$this->grupoSeleccionado}");
            
            // Obtener todas las asignaciones para este grupo
            $asignaciones = AsignacionTest::where('grupo_id', $this->grupoSeleccionado)
                ->where('profesor_id', Auth::user()->id)
                ->orderByDesc('created_at')
                ->get();
                
            if ($asignaciones->isEmpty()) {
                Log::warning("No se encontraron asignaciones para el grupo {$this->grupoSeleccionado}");
                $this->asignacionTestId = null;
                $this->resultadoAnalisis = "No se encontraron asignaciones de test para este grupo.";
                return;
            }
            
            // Buscar primero una asignación que tenga respuestas
            $asignacionConRespuestas = null;
            
            foreach ($asignaciones as $asignacion) {
                $tieneRespuestas = Respuesta::where('asignacion_test_id', $asignacion->id)->exists();
                if ($tieneRespuestas) {
                    $asignacionConRespuestas = $asignacion;
                    break;
                }
            }
            
            if ($asignacionConRespuestas) {
                $this->asignacionTestId = $asignacionConRespuestas->id;
                Log::info("Encontrada asignación con respuestas: ID {$this->asignacionTestId}");
                $this->resultadoAnalisis = "Se encontró una asignación con respuestas (ID: {$this->asignacionTestId}).";
            } else {
                // Si no hay ninguna con respuestas, usar la más reciente
                $this->asignacionTestId = $asignaciones->first()->id;
                Log::warning("No se encontraron asignaciones con respuestas. Usando la más reciente: ID {$this->asignacionTestId}");
                $this->resultadoAnalisis = "No hay respuestas registradas para las asignaciones de este grupo. No se podrán mostrar resultados.";
            }
        } else {
            $this->asignacionTestId = null;
        }
        
        // Reiniciamos los datos de resultados
        $this->jsonData = null;
        $this->labels = [];
        $this->data = [];
    }

    public function procesarAnalisis()
    {
        if (!$this->grupoSeleccionado || !$this->tipoAnalisis || !$this->asignacionTestId) {
            $this->resultadoAnalisis = "Por favor, seleccione un grupo y un tipo de análisis";
            return;
        }
    
        // Verificar que existan respuestas
        $tieneRespuestas = Respuesta::where('asignacion_test_id', $this->asignacionTestId)->exists();
        if (!$tieneRespuestas) {
            $this->resultadoAnalisis = "La asignación seleccionada no tiene respuestas registradas. No se pueden generar análisis.";
            return;
        }
    
        // 🔄 Generar relaciones desde las respuestas
        Relacion::generarDesdeRespuestas($this->asignacionTestId);
    
        $this->resultadoAnalisis = "Análisis procesado para el grupo seleccionado (Asignación ID: {$this->asignacionTestId})";
    
        // Procesar según el tipo de análisis seleccionado
                    // Procesar según el tipo de análisis seleccionado
            if ($this->tipoAnalisis == 'sociograma') {
                $this->generarSociograma();
            } elseif ($this->tipoAnalisis == 'recuento_preferencias') {
                $this->generarRecuentoPreferencias();
            } elseif ($this->tipoAnalisis == 'recuento_rechazos') {
                $this->generarRecuentoRechazos(); 
            
            } elseif ($this->tipoAnalisis == 'aislamiento') {
            $this->analisisAislamiento();
            }
        

                }


    private function generarRecuentoRechazos()
{
    try {
        $asignacionId = $this->asignacionTestId;

        $sql = "SELECT estudiantes.nombre, COUNT(*) as total 
                FROM relaciones 
                JOIN estudiantes ON relaciones.alumno_b_id = estudiantes.id 
                WHERE relaciones.asignacion_test_id = $asignacionId
                AND relaciones.tipo_relacion = 'rechazado' 
                GROUP BY estudiantes.nombre";

        $resultados = DB::select($sql);

        if (empty($resultados)) {
            $this->resultadoAnalisis = 'No se encontraron datos de rechazos para esta asignación.';
            return;
        }

        $labels = [];
        $data = [];

        foreach ($resultados as $row) {
            $labels[] = $row->nombre;
            $data[] = (int)$row->total;
        }

        $this->labels = $labels;
        $this->data = $data;

        $this->dispatch('actualizarGrafico', [
            'labels' => $labels,
            'data' => $data
        ]);

        $this->resultadoAnalisis = 'Recuento de rechazos generado correctamente.';

    } catch (\Exception $e) {
        Log::error('Error en el análisis de rechazos: ' . $e->getMessage());
        $this->resultadoAnalisis = 'Error en el análisis de rechazos: ' . $e->getMessage();
    }
}

    
    private function generarRecuentoPreferencias()
    {
        try {
            // Usar el ID de la asignación seleccionada, no un valor hardcodeado
            $asignacionId = $this->asignacionTestId;
            
            // Ejecutar consulta directa y capturar resultados
            $sql = "SELECT estudiantes.nombre, COUNT(*) as total 
                    FROM relaciones 
                    JOIN estudiantes ON relaciones.alumno_b_id = estudiantes.id 
                    WHERE relaciones.asignacion_test_id = $asignacionId
                    AND relaciones.tipo_relacion = 'preferido' 
                    GROUP BY estudiantes.nombre";
                    
            $resultados = DB::select($sql);
            
            // Registrar resultados crudos para depuración
            Log::info('SQL: ' . $sql);
            Log::info('Resultados crudos: ', [print_r($resultados, true)]);
            
            if (empty($resultados)) {
                Log::warning('La consulta no devolvió resultados');
                $this->resultadoAnalisis = 'No se encontraron datos de preferencias para esta asignación.';
                return;
            }
            
            // Procesar resultados
            $labels = [];
            $data = [];
            
            foreach ($resultados as $row) {
                Log::info('Procesando fila: ', [print_r($row, true)]);
                $labels[] = $row->nombre;
                $data[] = (int)$row->total;
            }
            
            $this->labels = $labels;
            $this->data = $data;
            
            Log::info('Datos procesados: ', ['labels' => $labels, 'data' => $data]);
            
            // Enviar datos al evento
            $this->dispatch('actualizarGrafico', [
                'labels' => $labels,
                'data' => $data
            ]);
            
            $this->resultadoAnalisis = 'Recuento de preferencias generado correctamente.';
            
        } catch (\Exception $e) {
            Log::error('Error en SQL: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            $this->resultadoAnalisis = 'Error en la consulta: ' . $e->getMessage();
        }
    }

    private function analisisAislamiento()
{
    try {
        $asignacionId = $this->asignacionTestId;

        // Obtener IDs de los estudiantes del grupo
        $estudiantesGrupo = Estudiante::whereIn('id', function ($query) {
            $query->select('id_estudiante')
                ->from('estudiantes_grupos')
                ->where('id_grupo', $this->grupoSeleccionado);
        })->get();
        
        // Obtener IDs de estudiantes que sí fueron elegidos al menos una vez como preferidos
        $preferidos = Relacion::where('asignacion_test_id', $asignacionId)
            ->where('tipo_relacion', 'preferido')
            ->pluck('alumno_b_id')
            ->unique()
            ->toArray();

        // Filtrar los estudiantes que NO están en la lista de preferidos
        $aislados = $estudiantesGrupo->filter(function ($estudiante) use ($preferidos) {
            return !in_array($estudiante->id, $preferidos);
        });

        if ($aislados->isEmpty()) {
            $this->resultadoAnalisis = "Todos los estudiantes fueron elegidos al menos una vez.";
        } else {
            $nombres = $aislados->pluck('nombre')->toArray();
            $this->resultadoAnalisis = "Estudiantes no elegidos por nadie: " . implode(', ', $nombres);
        }

    } catch (\Exception $e) {
        Log::error('Error en análisis de aislamiento: ' . $e->getMessage());
        $this->resultadoAnalisis = 'Error en el análisis de aislamiento: ' . $e->getMessage();
    }
}


private function generarSociograma()
{
    try {
        // Obtener el ID de la asignación seleccionada
        $asignacionId = $this->asignacionTestId;
        
        if (!$asignacionId) {
            $this->resultadoAnalisis = "No hay una asignación válida para generar el sociograma.";
            return;
        }
        
        // Verificar que existan relaciones para esta asignación
        $tieneRelaciones = Relacion::where('asignacion_test_id', $asignacionId)->exists();
        if (!$tieneRelaciones) {
            $this->resultadoAnalisis = "No hay relaciones registradas para esta asignación.";
            return;
        }
        
        // Obtener los estudiantes del grupo
        $estudiantes = Estudiante::whereIn('id', function ($query) {
            $query->select('id_estudiante')
                ->from('estudiantes_grupos')
                ->where('id_grupo', $this->grupoSeleccionado);
        })->get();
        
        if ($estudiantes->isEmpty()) {
            $this->resultadoAnalisis = "No hay estudiantes registrados en este grupo.";
            return;
        }
        
        // Preparar los nodos (estudiantes)
        $nodes = [];
        foreach ($estudiantes as $estudiante) {
            // Calcular métricas de popularidad para cada estudiante
            $preferencias = Relacion::where('asignacion_test_id', $asignacionId)
                            ->where('alumno_b_id', $estudiante->id)
                            ->where('tipo_relacion', 'preferido')
                            ->count();
            
            $rechazos = Relacion::where('asignacion_test_id', $asignacionId)
                            ->where('alumno_b_id', $estudiante->id)
                            ->where('tipo_relacion', 'rechazado')
                            ->count();
            
            $totalRelaciones = $preferencias + $rechazos;
            
            // Calcular índice de popularidad (entre 0 y 1)
            $popularidad = $totalRelaciones > 0 ? $preferencias / $totalRelaciones : 0;
            
            $nodes[] = [
                'id' => $estudiante->id,
                'label' => $estudiante->nombre,
                'title' => $estudiante->apellidos ? $estudiante->nombre . ' ' . $estudiante->apellidos : $estudiante->nombre,
                'metricas' => [
                    'preferencias' => $preferencias,
                    'rechazos' => $rechazos,
                    'popularidad' => $popularidad
                ]
            ];
        }
        
        // Obtener las relaciones
        $relaciones = Relacion::where('asignacion_test_id', $asignacionId)
            ->with(['alumnoA', 'alumnoB'])
            ->get();
        
        // Preparar los enlaces (relaciones)
        $links = [];
        foreach ($relaciones as $relacion) {
            $links[] = [
                'source' => $relacion->alumno_a_id,
                'target' => $relacion->alumno_b_id,
                'tipo_relacion' => $relacion->tipo_relacion === 'rechazado' ? 'rechazo' : 'preferido',
                'intensidad' => $relacion->intensidad ?? 1
            ];
        }
        
        // Guardar los datos del sociograma
        $this->jsonData = [
            'nodes' => $nodes,
            'links' => $links
        ];
        
        // Registrar información para depuración
        Log::info('Datos del sociograma generados:', [
            'nodes' => count($nodes),
            'links' => count($links)
        ]);
        
        // Emitir el evento para actualizar el sociograma en el frontend
        $this->dispatch('actualizarSociograma', $this->jsonData);
        
        $this->resultadoAnalisis = "Sociograma generado con " . count($nodes) . " estudiantes y " . count($links) . " relaciones.";
        
    } catch (\Exception $e) {
        Log::error('Error al generar sociograma: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        $this->resultadoAnalisis = 'Error al generar el sociograma: ' . $e->getMessage();
    }
}
    public function render()
    {
        return view('livewire.analisis-selector');
    }
}