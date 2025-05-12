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
    public $grupos;
    public $grupoSeleccionado = '';

    public $asignaciones = [];
    public $asignacionTestId = null;

    public array $analisis = [];
    public string $resultadoAnalisis = '';

    public $jsonData = null;

    public function mount(): void
    {
        $this->grupos = Grupo::where('id_profesor', Auth::id())->get();
        $this->asignaciones = [];
        $this->analisis = [];
    }

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

        $this->analisis = [
            'sociograma'   => $full['sociograma'],
            'preferencias' => $full['preferencias'],
            'rechazos'     => $full['rechazos'],
            'aislamiento'  => $full['aislamiento'],
        ];

        // Dispatch a los gráficos existentes
        $this->dispatch('actualizarGraficoPreferencias', $full['preferencias']);
        $this->dispatch('actualizarGraficoRechazos',     $full['rechazos']);
        $this->dispatch('actualizarSociograma',          $full['sociograma']);

        // Matriz de reciprocidad
        $matrizRec = $this->matrizReciprocidad();  // ['labels' => [...], 'data' => [...]]
        $this->analisis['reciprocidad'] = $matrizRec;
        $this->dispatch('actualizarMatrizReciprocidad', $matrizRec); // Evento para la matriz

        $this->resultadoAnalisis = 'Análisis generado correctamente.';
    }

    private function datosSociograma(): array
    {
        $estudiantes = Estudiante::whereIn('id', function ($q) {
            $q->select('id_estudiante')
              ->from('estudiantes_grupos')
              ->where('id_grupo', $this->grupoSeleccionado);
        })->get();

        $allRelations = Relacion::where('asignacion_test_id', $this->asignacionTestId)
                               ->get();

        $prefAll = [];
        $rechAll = [];

        $nodes = $estudiantes->map(function ($e) use ($allRelations, &$prefAll, &$rechAll) {
            $pref = $allRelations->where('alumno_b_id', $e->id)
                                 ->where('tipo_relacion', 'preferido')->count();
            $rech = $allRelations->where('alumno_b_id', $e->id)
                                 ->where('tipo_relacion', 'rechazado')->count();

            $prefAll[$e->nombre] = $pref;
            $rechAll[$e->nombre] = $rech;

            return [
                'id'       => $e->id,
                'label'    => $e->nombre,
                'metricas' => [
                    'preferencias_recibidas' => $pref,
                    'rechazos_recibidos'     => $rech,
                    'popularidad'            => ($pref + $rech)
                                                 ? $pref / ($pref + $rech)
                                                 : 0,
                ],
            ];
        })->values();

        $links = $allRelations->map(fn ($r) => [
            'source'        => $r->alumno_a_id,
            'target'        => $r->alumno_b_id,
            'tipo_relacion' => $r->tipo_relacion === 'rechazado' ? 'rechazo' : 'preferido',
            'intensidad'    => $r->intensidad ?? 1,
        ])->values();

        $this->jsonData = [
            'nodes' => $nodes->toArray(),
            'links' => $links->toArray(),
        ];

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

    private function matrizReciprocidad(): array
    {
        // 1. Lista de estudiantes del grupo
        $alumnos = Estudiante::whereIn('id', function ($q) {
            $q->select('id_estudiante')
              ->from('estudiantes_grupos')
              ->where('id_grupo', $this->grupoSeleccionado);
        })
        ->orderBy('nombre')
        ->get();
    
        // Mapa para índices y etiquetas
        $labels = $alumnos->pluck('nombre')->toArray();
        $indexOf = $alumnos->pluck('id')->flip();  // ej. [12=>0, 17=>1, …]
        $idsGrupo = $alumnos->pluck('id');
    
        // 2. Matriz inicializada en 0
        $n = count($labels);
        $M = array_fill(0, $n, array_fill(0, $n, 0));
    
        // 3. Relación solo entre alumnos del grupo (sin self-loops)
        $rel = Relacion::where('asignacion_test_id', $this->asignacionTestId)
            ->whereColumn('alumno_a_id', '<>', 'alumno_b_id')
            ->whereIn('alumno_a_id', $idsGrupo)
            ->whereIn('alumno_b_id', $idsGrupo)
            ->get();
    
        // 4. Recorremos y guardamos relaciones dirigidas
        $dir = [];   // $dir["i-j"] = tipo_relacion
        foreach ($rel as $r) {
            if (!isset($indexOf[$r->alumno_a_id]) || !isset($indexOf[$r->alumno_b_id])) {
                continue;
            }
            $i = $indexOf[$r->alumno_a_id];
            $j = $indexOf[$r->alumno_b_id];
            $dir["$i-$j"] = $r->tipo_relacion;
        }
    
        // 5. Calculamos códigos de reciprocidad
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if ($i === $j) {
                    $M[$i][$j] = 0;
                    continue;
                }
    
                $AB = $dir["$i-$j"] ?? null;
                $BA = $dir["$j-$i"] ?? null;
    
                if ($AB === 'preferido' && $BA === 'preferido')        $v = 4;
                elseif ($AB === 'rechazado' && $BA === 'rechazado')    $v = 3;
                elseif ($AB === 'preferido' && $BA === 'rechazado'
                     || $AB === 'rechazado' && $BA === 'preferido')    $v = 5;
                elseif ($AB === 'preferido' || $BA === 'preferido')    $v = 2;
                elseif ($AB === 'rechazado' || $BA === 'rechazado')    $v = 1;
                else                                                   $v = 0;
    
                $M[$i][$j] = $v;
            }
        }
    
        // 6. Convertimos a estructura compatible con el gráfico
        $data = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $data[] = ['x' => $j, 'y' => $i, 'v' => $M[$i][$j]];
            }
        }
    
        return compact('labels', 'data');
    }
    

    public function render()
    {
        return view('livewire.analisis-selector', [
            'grupos'              => $this->grupos,
            'asignaciones'        => $this->asignaciones,
            'resultadoAnalisis' => $this->resultadoAnalisis,
            'analisis'            => $this->analisis,
        ]);
    }
}