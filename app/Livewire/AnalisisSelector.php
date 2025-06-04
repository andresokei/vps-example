<?php
/**
 *  app/Livewire/AnalisisSelector.php
 *  ─────────────────────────────────
 *  Genera sociograma + métricas y las envía al front.
 */

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\{
    Grupo,
    AsignacionTest,
    Relacion,
    Estudiante,
    Respuesta,
    Pregunta 
};

class AnalisisSelector extends Component
{
    /*════════════  PROPIEDADES  ════════════*/
    public $grupos;
    public $grupoSeleccionado = '';

    public $asignaciones = [];
    public $asignacionTestId = null;

    public array $analisis= [];
    public string $resultadoAnalisis = '';
    public $updateTrigger = null;

    /*════════════  CICLO DE VIDA  ════════════*/
    public function mount(): void
    {
        $this->grupos = Grupo::where('id_profesor', Auth::id())->get();
    }

    public function updatedGrupoSeleccionado(): void
    {
        $this->reset(['asignacionTestId','asignaciones','resultadoAnalisis','analisis']);

        if (!$this->grupoSeleccionado) return;

        $this->asignaciones = AsignacionTest::where('grupo_id', $this->grupoSeleccionado)
            ->where('profesor_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        if ($this->asignaciones->isEmpty()) {
            $this->resultadoAnalisis = 'No se encontraron asignaciones de test para este grupo.'; return;
        }

        $this->asignacionTestId = $this->asignaciones
            ->firstWhere(fn ($a) => Respuesta::where('asignacion_test_id', $a->id)->exists())
            ?->id ?? $this->asignaciones->first()->id;
    }

    /*════════════  BOTÓN “ANALIZAR”  ════════════*/
   public function procesarAnalisis(): void
{
    if (!$this->grupoSeleccionado || !$this->asignacionTestId) {
        $this->resultadoAnalisis = 'Seleccione grupo y asignación.';
        return;
    }

    if (!Respuesta::where('asignacion_test_id', $this->asignacionTestId)->exists()) {
        $this->resultadoAnalisis = 'La asignación seleccionada no tiene respuestas.';
        return;
    }

    // Regenera la tabla de relaciones a partir de las respuestas
    Relacion::generarDesdeRespuestas($this->asignacionTestId);

    // Realiza los cálculos y genera los datos del análisis
    $full = $this->datosSociograma();
    $full['reciprocidad'] = $this->matrizReciprocidad();
    $this->analisis = $full; // Asignación de los datos completos

    // Forzamos a Livewire a detectar el cambio (si hace falta)
    $this->updateTrigger = now(); // Puede ser un timestamp, un booleano, etc.

    // Dispara eventos hacia el frontend para actualizar visualizaciones
    $this->dispatch('actualizarSociograma', $full['sociograma']);
    $this->dispatch('actualizarGraficoPreferencias', $full['preferencias']);
    $this->dispatch('actualizarGraficoRechazos', $full['rechazos']);
    $this->dispatch('actualizarMatrizReciprocidad', $full['reciprocidad']);

    // Mensaje de éxito
    $this->resultadoAnalisis = 'Análisis generado correctamente.';
}



    /*════════════  GRAFO + BARRAS + PREGUNTAS  ════════════*/
    private function datosSociograma(): array
    {
        /* 1. Alumnos del grupo */
        $alumnos = Estudiante::whereIn('id', function ($q) {
            $q->select('id_estudiante')->from('estudiantes_grupos')
              ->where('id_grupo', $this->grupoSeleccionado);
        })->orderBy('nombre')->get();

        $idsGrupo = $alumnos->pluck('id');

        /* 2. Relaciones (todas) */
        $rel = Relacion::where('asignacion_test_id', $this->asignacionTestId)->get();

        /* 3. Mapa dirigido para matches */
        $dir = $rel->mapWithKeys(fn ($r) =>
            [$r->alumno_a_id.'-'.$r->alumno_b_id => $r->tipo_relacion]);

        /* 4. Aristas */
        $links = $rel->map(function ($r) use ($dir) {
            $recip = $dir[$r->alumno_b_id.'-'.$r->alumno_a_id] ?? null;
            return [
                'source' => $r->alumno_a_id,
                'target'=> $r->alumno_b_id,
                'tipo' => $r->tipo_relacion,
                'isMatch'  => $recip === 'preferido' && $r->tipo_relacion === 'preferido',
                'pregunta_id' => $r->pregunta_id  // NEW
            ];
        });

        /* 5. Conteos recibidos */
        $prefRec = $rel->where('tipo_relacion','preferido')
                         ->whereIn('alumno_b_id', $idsGrupo)
                         ->countBy('alumno_b_id');

        $rechRec = $rel->where('tipo_relacion','rechazado')
                         ->whereIn('alumno_b_id', $idsGrupo)
                         ->countBy('alumno_b_id');

        /* 6. Nodos */
        $nodes = $alumnos->map(fn ($a) => [
            'id'  => $a->id,
            'label'   => $a->nombre,
            'isIsolate' => !($prefRec[$a->id] ?? 0),
            'metricas'  => [
                'preferencias_recibidas' => $prefRec[$a->id] ?? 0,
                'rechazos_recibidos'  => $rechRec[$a->id] ?? 0,
            ],
        ]);

        /* 7. Preguntas distintas (para filtro)  NEW */
        $preguntas = Pregunta::whereIn('id', $links->pluck('pregunta_id')->unique())
    ->orderBy('texto_pregunta')
    ->get(['id','texto_pregunta as texto'])
    ->map(fn($p) => [
        'id'    => $p->id,
        'texto' => $p->texto,
    ])
    ->values()
    ->toArray();


        /* 8. Datos para Chart.js */
        $prefGraf = $prefRec->filter()->sortKeys()->mapWithKeys(function ($v,$id) use ($alumnos) {
            $nombre = optional($alumnos->firstWhere('id',$id))->nombre;
            return $nombre ? [$nombre => $v] : [];
        });
        $rechGraf = $rechRec->filter()->sortKeys()->mapWithKeys(function ($v,$id) use ($alumnos) {
            $nombre = optional($alumnos->firstWhere('id',$id))->nombre;
            return $nombre ? [$nombre => $v] : [];
        });

        return [
            'sociograma' => [
                'nodes' => $nodes->values()->toArray(),
                'links'   => $links->values()->toArray(),
                'preguntas' => $preguntas,  // NEW: Aseguramos que 'preguntas' está aquí
            ],
            'preferencias' => [
                'labels' => $prefGraf->keys()->values(),
                'data'  => $prefGraf->values(),
            ],
            'rechazos'  => [
                'labels' => $rechGraf->keys()->values(),
                'data'  => $rechGraf->values(),
            ],
        ];
    }

    /*════════════  MATRIZ DE RECIPROCIDAD  ════════════*/
    private function matrizReciprocidad(): array
    {
        $alumnos = Estudiante::whereIn('id', function ($q) {
            $q->select('id_estudiante')->from('estudiantes_grupos')
              ->where('id_grupo', $this->grupoSeleccionado);
        })->orderBy('nombre')->get();

        $labels = $alumnos->pluck('nombre')->values()->toArray();
        $indexOf = $alumnos->pluck('id')->flip();
        $ids = $alumnos->pluck('id');
        $n  = count($labels);

        $M = array_fill(0,$n,array_fill(0,$n,0));

        $rel = Relacion::where('asignacion_test_id', $this->asignacionTestId)
            ->whereColumn('alumno_a_id', '<>', 'alumno_b_id')
            ->whereIn('alumno_a_id', $ids)
            ->whereIn('alumno_b_id', $ids)
            ->get();

        $dir=[];
        foreach ($rel as $r) {
            $i=$indexOf[$r->alumno_a_id]; $j=$indexOf[$r->alumno_b_id];
            $dir["$i-$j"]=$r->tipo_relacion;
        }

        for($i=0;$i<$n;$i++){
            for($j=0;$j<$n;$j++){
                if($i===$j){$M[$i][$j]=0;continue;}
                $AB=$dir["$i-$j"]??null; $BA=$dir["$j-$i"]??null;
                $M[$i][$j] = match(true){
                    $AB==='preferido' && $BA==='preferido' => 4,
                    $AB==='rechazado' && $BA==='rechazado' => 3,
                    $AB==='preferido' && $BA==='rechazado',
                    $AB==='rechazado' && $BA==='preferido'   => 5,
                    $AB==='preferido'||$BA==='preferido'   => 2,
                    $AB==='rechazado'||$BA==='rechazado'   => 1,
                    default  => 0
                };
            }
        }

        $data=[];
        for($i=0;$i<$n;$i++){
            for($j=0;$j<$n;$j++){
                $data[]=['x'=>$j,'y'=>$i,'v'=>$M[$i][$j]];
            }
        }
        return compact('labels','data');
    }

    /*════════════  RENDER  ════════════*/
    public function render()
    {
        // <-- Añade este bloque condicional al principio del método render()
        // if (isset($this->analisis['sociograma']['preguntas']) && is_array($this->analisis['sociograma']['preguntas']) && !empty($this->analisis['sociograma']['preguntas'])) {
        //      dd($this->analisis); // <-- SEGUNDO DD: Este se activará en el render posterior si hay preguntas
        // }
        // --> Fin del bloque condicional


        return view('livewire.analisis-selector', [
            'grupos'  => $this->grupos,
            'asignaciones'  => $this->asignaciones,
            'resultadoAnalisis' => $this->resultadoAnalisis,
            'analisis'  => $this->analisis, // Aquí se pasa $this->analisis a la vista como 'analisis'
        ]);
    }
}