<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
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

    public $asignaciones = [];
    public $asignacionTestId = null;

    public array $analisis = [];
    public string $resultadoAnalisis = '';

    /* datos extra para sociograma */
    public $jsonData = null;

    /* ------------------------------------------------------------
     |  Ciclo de vida                                              |
     * -----------------------------------------------------------*/
    public function mount(): void
    {
        $this->grupos = Grupo::where('id_profesor', Auth::id())->get();
        $this->asignaciones = [];
        $this->analisis = [];
    }

    /**
     * Cuando se selecciona un grupo cargamos sus asignaciones.
     */
    public function updatedGrupoSeleccionado(): void
    {
        $this->reset(['asignacionTestId', 'asignaciones', 'resultadoAnalisis', 'analisis', 'jsonData']);

        if (! $this->grupoSeleccionado) {
            return;
        }

        $this->asignaciones = AsignacionTest::where('grupo_id', $this->grupoSeleccionado)
            ->where('profesor_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        if ($this->asignaciones->isEmpty()) {
            $this->resultadoAnalisis = 'No se encontraron asignaciones de test para este grupo.';
            return;
        }

        // Selecciona la primera con respuestas (o la más reciente)
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
        if (! $this->grupoSeleccionado || ! $this->asignacionTestId) {
            $this->resultadoAnalisis = 'Seleccione grupo y asignación.';
            return;
        }

        if (! Respuesta::where('asignacion_test_id', $this->asignacionTestId)->exists()) {
            $this->resultadoAnalisis = 'La asignación seleccionada no tiene respuestas.';
            return;
        }

        // Regenera relaciones
        Relacion::generarDesdeRespuestas($this->asignacionTestId);

        // Genera un único análisis completo
        $full = $this->datosSociograma();

        // Asigna todas las secciones en orden: sociograma primero
        $this->analisis = [
            'sociograma'   => $full['sociograma'],
            'preferencias' => $full['preferencias'],
            'rechazos'     => $full['rechazos'],
            'aislamiento'  => $full['aislamiento'],
        ];

        // Dispara eventos JS
        $this->dispatch('actualizarGraficoPreferencias', $full['preferencias']);
        $this->dispatch('actualizarGraficoRechazos',     $full['rechazos']);
        $this->dispatch('actualizarSociograma',          $full['sociograma']);

        $this->resultadoAnalisis = 'Análisis generado correctamente.';
    }

    /* ------------------------------------------------------------
     |  Genera TODO el análisis en un solo método                |
     * -----------------------------------------------------------*/
    private function datosSociograma(): array
    {
        // 1️⃣ Estudiantes del grupo
        $estudiantes = Estudiante::whereIn('id', function ($q) {
            $q->select('id_estudiante')
              ->from('estudiantes_grupos')
              ->where('id_grupo', $this->grupoSeleccionado);
        })->get();

        // 2️⃣ Todas las relaciones de la asignación
        $allRelations = Relacion::where('asignacion_test_id', $this->asignacionTestId)
                                ->get();

        // 3️⃣ Contadores para todo
        $prefAll = [];
        $rechAll = [];

        // 4️⃣ Construir nodos y acumular conteos
        $nodes = $estudiantes->map(function ($e) use ($allRelations, &$prefAll, &$rechAll) {
            $pref = $allRelations->where('alumno_b_id', $e->id)
                                 ->where('tipo_relacion', 'preferido')->count();
            $rech = $allRelations->where('alumno_b_id', $e->id)
                                 ->where('tipo_relacion', 'rechazado')->count();

            $prefAll[$e->nombre] = $pref;
            $rechAll[$e->nombre] = $rech;

            return [
                'id'      => $e->id,
                'label'   => $e->nombre,
                'metricas'=> [
                    'preferencias_recibidas' => $pref,
                    'rechazos_recibidos'     => $rech,
                    'popularidad'            => ($pref + $rech)
                                                ? $pref / ($pref + $rech)
                                                : 0,
                ],
            ];
        })->values();

        // 5️⃣ Construir enlaces
        $links = $allRelations->map(fn ($r) => [
            'source'        => $r->alumno_a_id,
            'target'        => $r->alumno_b_id,
            'tipo_relacion' => $r->tipo_relacion === 'rechazado' ? 'rechazo' : 'preferido',
            'intensidad'    => $r->intensidad ?? 1,
        ])->values();

        // 6️⃣ Sociograma JSON
        $this->jsonData = [
            'nodes' => $nodes->toArray(),
            'links' => $links->toArray(),
        ];

        // 7️⃣ Ordenar por nombre y filtrar para gráficas
        ksort($prefAll);
        ksort($rechAll);
        $prefGraf = array_filter($prefAll, fn ($v) => $v > 0);
        $rechGraf = array_filter($rechAll, fn ($v) => $v > 0);

        return [
            'sociograma'   => $this->jsonData,
            'preferencias' => [
                'labels' => array_keys($prefGraf),
                'data'   => array_values($prefGraf),
            ],
            'rechazos'     => [
                'labels' => array_keys($rechGraf),
                'data'   => array_values($rechGraf),
            ],
            'aislamiento'  => array_keys(
                array_filter($prefAll, fn ($v) => $v === 0)
            ),
        ];
    }

    /* ------------------------------------------------------------ */
    public function render()
    {
        return view('livewire.analisis-selector', [
            'grupos'            => $this->grupos,
            'asignaciones'      => $this->asignaciones,
            'resultadoAnalisis' => $this->resultadoAnalisis,
            'analisis'          => $this->analisis,
        ]);
    }
}
