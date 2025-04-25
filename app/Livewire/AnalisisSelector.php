<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Grupo;
use App\Models\AsignacionTest;
use App\Models\Relacion;
use App\Models\Estudiante;
use App\Models\Respuesta;

class AnalisisSelector extends Component
{
    /* ------------------------------------------------------------
     |  Propiedades públicas (se serializan en el front)          |
     * -----------------------------------------------------------*/
    public $grupos;
    public $grupoSeleccionado = '';

    public $asignaciones      = [];   // lista de tests del grupo
    public $asignacionTestId  = null; // id del test elegido

    public array  $analisis   = [];   // resultados a pintar en la vista
    public string $resultadoAnalisis = '';

    /* datos extra para sociograma */
    public $jsonData = null;

    /* ------------------------------------------------------------
     |  Ciclo de vida                                              |
     * -----------------------------------------------------------*/
    public function mount(): void
    {
        $this->grupos       = Grupo::where('id_profesor', Auth::id())->get();
        $this->asignaciones = [];
        $this->analisis     = [];
    }

    /**
     * Cuando se selecciona un grupo cargamos sus asignaciones.
     */
    public function updatedGrupoSeleccionado(): void
    {
        // Limpiar estado anterior
        $this->reset('asignacionTestId', 'asignaciones', 'resultadoAnalisis',
                     'analisis', 'jsonData');

        if (!$this->grupoSeleccionado) return;

        $this->asignaciones = AsignacionTest::where('grupo_id', $this->grupoSeleccionado)
            ->where('profesor_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        if ($this->asignaciones->isEmpty()) {
            $this->resultadoAnalisis = 'No se encontraron asignaciones de test para este grupo.';
            return;
        }

        // Seleccionamos la primera asignación con respuestas (o la más reciente)
        $this->asignacionTestId = $this->asignaciones->first()->id;
        foreach ($this->asignaciones as $a) {
            if (Respuesta::where('asignacion_test_id', $a->id)->exists()) {
                $this->asignacionTestId = $a->id;
                break;
            }
        }
    }

    /* ------------------------------------------------------------
     |  Procesar análisis                                          |
     * -----------------------------------------------------------*/
    public function procesarAnalisis(): void
    {
        // Validaciones mínimas
        if (!$this->grupoSeleccionado || !$this->asignacionTestId) {
            $this->resultadoAnalisis = 'Seleccione grupo y asignación.';
            return;
        }

        if (!Respuesta::where('asignacion_test_id', $this->asignacionTestId)->exists()) {
            $this->resultadoAnalisis = 'La asignación seleccionada no tiene respuestas.';
            return;
        }

        // Generar / actualizar tabla relaciones
        Relacion::generarDesdeRespuestas($this->asignacionTestId);

        /* 1️⃣  Ejecutamos los 4 análisis y guardamos los datos */
        $preferencias = $this->recuentoPreferencias();
        $rechazos     = $this->recuentoRechazos();
        $sociograma   = $this->datosSociograma();
        $aislamiento  = $this->listaAislados();

        $this->analisis = [
            'sociograma'   => $sociograma,
            'preferencias' => $preferencias,
            'rechazos'     => $rechazos,
            'aislamiento'  => $aislamiento,
        ];

        /* 2️⃣  Emitimos eventos JS */
        $this->dispatch('actualizarGraficoPreferencias', $preferencias);
        $this->dispatch('actualizarGraficoRechazos',     $rechazos);
        $this->dispatch('actualizarSociograma',          $sociograma);

        $this->resultadoAnalisis = 'Análisis generado correctamente.';
    }

    /* ------------------------------------------------------------
     |  Métodos que DEVUELVEN datos                                |
     * -----------------------------------------------------------*/

     private function recuentoPreferencias(): array
{
    $rows = DB::select(
        "SELECT e.nombre, COUNT(*) total
         FROM relaciones r
         JOIN estudiantes e ON r.alumno_b_id = e.id
         WHERE r.asignacion_test_id = ?
           AND r.tipo_relacion = 'preferido'
         GROUP BY e.nombre",
        [$this->asignacionTestId]
    );

    $labels = array_map(fn($r) => $r->nombre, $rows);
    $data   = array_map(fn($r) => (int) $r->total, $rows);

    return compact('labels', 'data');
}

private function recuentoRechazos(): array
{
    $rows = DB::select(
        "SELECT e.nombre, COUNT(*) total
         FROM relaciones r
         JOIN estudiantes e ON r.alumno_b_id = e.id
         WHERE r.asignacion_test_id = ?
           AND r.tipo_relacion = 'rechazado'
         GROUP BY e.nombre",
        [$this->asignacionTestId]
    );

    $labels = array_map(fn($r) => $r->nombre, $rows);
    $data   = array_map(fn($r) => (int) $r->total, $rows);

    return compact('labels', 'data');
}

     

    private function listaAislados(): array
    {
        $todos = Estudiante::whereIn('id', function ($q) {
            $q->select('id_estudiante')
              ->from('estudiantes_grupos')
              ->where('id_grupo', $this->grupoSeleccionado);
        })->pluck('id');

        $preferidos = Relacion::where('asignacion_test_id', $this->asignacionTestId)
            ->where('tipo_relacion', 'preferido')
            ->pluck('alumno_b_id')
            ->unique();

        return Estudiante::whereIn('id', $todos->diff($preferidos))
                 ->pluck('nombre')
                 ->toArray();
    }

    private function datosSociograma(): array
    {
        /* Nodos */
        $estudiantes = Estudiante::whereIn('id', function ($q) {
            $q->select('id_estudiante')
              ->from('estudiantes_grupos')
              ->where('id_grupo', $this->grupoSeleccionado);
        })->get();

        $nodes = $estudiantes->map(function ($e) {
            $pref = Relacion::where('asignacion_test_id', $this->asignacionTestId)
                     ->where('alumno_b_id', $e->id)
                     ->where('tipo_relacion', 'preferido')
                     ->count();
            $rech = Relacion::where('asignacion_test_id', $this->asignacionTestId)
                     ->where('alumno_b_id', $e->id)
                     ->where('tipo_relacion', 'rechazado')
                     ->count();
            $pop  = ($pref + $rech) ? $pref / ($pref + $rech) : 0;

            return [
                'id'    => $e->id,
                'label' => $e->nombre,
                'metricas' => [
                    'preferencias_recibidas' => $pref,
                    'rechazos_recibidos'     => $rech,
                    'popularidad'  => $pop,
                ],
            ];
        })->values();

        /* Enlaces */
        $links = Relacion::where('asignacion_test_id', $this->asignacionTestId)
            ->get()
            ->map(fn ($r) => [
                'source'        => $r->alumno_a_id,
                'target'        => $r->alumno_b_id,
                'tipo_relacion' => $r->tipo_relacion == 'rechazado' ? 'rechazo' : 'preferido',
                'intensidad'    => $r->intensidad ?? 1,
            ])->values();

        $this->jsonData = [
                'nodes' => $nodes->toArray(),   // ⬅️  <— aquí
                'links' => $links->toArray(),   // ⬅️  <— y aquí

        ]; // por si la vista lo usa
        return $this->jsonData;
    }

    /* ------------------------------------------------------------ */
    public function render()
    {
        // Pasamos las propiedades que la vista necesita para renderizar
        return view('livewire.analisis-selector', [
            'grupos' => $this->grupos,
            'asignaciones' => $this->asignaciones,
            'resultadoAnalisis' => $this->resultadoAnalisis,
            'analisis' => $this->analisis, // Pasa el array de análisis
             // Puedes pasar otras propiedades si la vista las necesita directamente
        ]);
    }
}
