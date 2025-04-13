<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class RecuentoPreferencias extends Component
{
    public $asignacionTestId;
    public $labels = [];
    public $data = [];
    public $mensaje = '';
    
    // Escuchar eventos del selector de análisis
    protected $listeners = ['actualizarGrafico' => 'actualizarDatos'];

    public function mount($asignacionTestId = null)
    {
        $this->asignacionTestId = $asignacionTestId;
        
        // Inicializar con datos vacíos
        $this->labels = [];
        $this->data = [];
    }
    
    // Este método recibe los datos actualizados desde AnalisisSelector
    public function actualizarDatos($datos = null)
    {
        if ($datos) {
            $this->labels = $datos['labels'] ?? [];
            $this->data = $datos['data'] ?? [];
        }
    }

    public function procesarAnalisis()
    {
        if (!$this->asignacionTestId) {
            $this->mensaje = 'No hay asignación de test seleccionada.';
            return;
        }
        
        try {
            // Obtener los datos desde la base de datos
            $resultados = DB::table('relaciones')
                ->join('estudiantes', 'relaciones.alumno_b_id', '=', 'estudiantes.id')
                ->select('estudiantes.nombre', DB::raw('count(*) as total'))
                ->where('relaciones.asignacion_test_id', $this->asignacionTestId)
                ->where('relaciones.tipo_relacion', 'preferido')
                ->groupBy('estudiantes.nombre')
                ->get();

            if ($resultados->count() > 0) {
                $this->labels = $resultados->pluck('nombre')->toArray();
                $this->data = $resultados->pluck('total')->toArray();
                $this->mensaje = 'Análisis procesado correctamente.';
            } else {
                // Datos de prueba en caso de que no haya resultados
                $this->labels = ['Estudiante 1', 'Estudiante 2', 'Estudiante 3', 'Estudiante 4', 'Estudiante 5'];
                $this->data = [5, 8, 2, 6, 3];
                $this->mensaje = 'No se encontraron datos. Mostrando valores de ejemplo.';
            }
            
            // Lanzar evento para actualizar el gráfico (si lo necesitas)
            $this->dispatch('actualizarGraficoUI');
            
        } catch (\Exception $e) {
            // En caso de error, usar datos de prueba
            $this->labels = ['Error'];
            $this->data = [0];
            $this->mensaje = 'Error al procesar análisis: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.recuento-preferencias');
    }
}