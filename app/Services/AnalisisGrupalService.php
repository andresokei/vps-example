<?php

namespace App\Services;

use App\Models\AsignacionTest;
use App\Models\Estudiante;
use App\Models\Pregunta;
use App\Models\Relacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AnalisisGrupalService
{
    public function invalidateForAssignmentId(int $asignacionTestId): void
    {
        $asignacion = AsignacionTest::query()
            ->select(['id', 'grupo_id'])
            ->find($asignacionTestId);

        if (! $asignacion) {
            return;
        }

        $this->invalidateForAssignment((int) $asignacion->grupo_id, (int) $asignacion->id);
    }

    public function invalidateForAssignment(int $grupoId, int $asignacionTestId): void
    {
        Cache::forget($this->cacheKey($grupoId, $asignacionTestId));
    }

    public function generar(int $grupoId, int $asignacionTestId, bool $forceRefresh = false): array
    {
        $cacheKey = $this->cacheKey($grupoId, $asignacionTestId);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
            Log::info('AnalisisGrupal: cache invalidado', [
                'grupo_id' => $grupoId,
                'asignacion_test_id' => $asignacionTestId,
            ]);
        }

        $hit = Cache::has($cacheKey);

        $result = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($grupoId, $asignacionTestId) {
            return $this->generarSinCache($grupoId, $asignacionTestId);
        });

        if ($hit) {
            Log::debug('AnalisisGrupal: resultado servido desde cache', [
                'grupo_id' => $grupoId,
                'asignacion_test_id' => $asignacionTestId,
            ]);
        }

        return $result;
    }

    private function generarSinCache(int $grupoId, int $asignacionTestId): array
    {
        $start = microtime(true);
        Log::info('AnalisisGrupal: iniciando calculo', [
            'grupo_id' => $grupoId,
            'asignacion_test_id' => $asignacionTestId,
        ]);

        $alumnos = $this->obtenerAlumnosGrupo($grupoId);
        $idsGrupo = $alumnos->pluck('id');
        $idsGrupoArray = $idsGrupo->all();

        $rel = Relacion::where('asignacion_test_id', $asignacionTestId)
            ->whereIn('alumno_a_id', $idsGrupoArray)
            ->whereIn('alumno_b_id', $idsGrupoArray)
            ->whereColumn('alumno_a_id', '<>', 'alumno_b_id')
            ->get();

        $dir = $rel->mapWithKeys(fn ($r) => [
            $r->alumno_a_id . '-' . $r->alumno_b_id => $r->tipo_relacion,
        ]);

        $links = $rel->map(function ($r) use ($dir) {
            $recip = $dir[$r->alumno_b_id . '-' . $r->alumno_a_id] ?? null;

            return [
                'source' => (int) $r->alumno_a_id,
                'target' => (int) $r->alumno_b_id,
                'tipo' => $r->tipo_relacion,
                'isMatch' => $recip === 'preferido' && $r->tipo_relacion === 'preferido',
                'pregunta_id' => (int) $r->pregunta_id,
            ];
        })->values();

        $prefRec = $rel->where('tipo_relacion', 'preferido')->countBy('alumno_b_id');
        $rechRec = $rel->where('tipo_relacion', 'rechazado')->countBy('alumno_b_id');
        $prefOut = $rel->where('tipo_relacion', 'preferido')->countBy('alumno_a_id');
        $rechOut = $rel->where('tipo_relacion', 'rechazado')->countBy('alumno_a_id');

        $nodes = $alumnos->map(fn ($a) => [
            'id' => (int) $a->id,
            'label' => $a->nombre,
            'isIsolate' => (($prefRec[$a->id] ?? 0) + ($rechRec[$a->id] ?? 0) + ($prefOut[$a->id] ?? 0) + ($rechOut[$a->id] ?? 0)) === 0,
            'metricas' => [
                'preferencias_recibidas' => (int) ($prefRec[$a->id] ?? 0),
                'rechazos_recibidos' => (int) ($rechRec[$a->id] ?? 0),
                'preferencias_emitidas' => (int) ($prefOut[$a->id] ?? 0),
                'rechazos_emitidos' => (int) ($rechOut[$a->id] ?? 0),
            ],
        ])->values();

        $preguntas = Pregunta::whereIn('id', $links->pluck('pregunta_id')->filter()->unique()->values())
            ->orderBy('texto_pregunta')
            ->get(['id', 'texto_pregunta', 'texto_pregunta_en'])
            ->map(fn ($p) => ['id' => (int) $p->id, 'texto' => $p->localized_texto_pregunta])
            ->values()
            ->toArray();

        $prefGraf = $this->prepararSerieBarras($alumnos, $prefRec);
        $rechGraf = $this->prepararSerieBarras($alumnos, $rechRec);

        $grafo = [
            'nodes' => $nodes->toArray(),
            'links' => $links->toArray(),
            'preguntas' => $preguntas,
        ];

        $metricas = $this->calcularMetricasRed($alumnos, $rel);
        $centralidades = $this->calcularCentralidades($alumnos, $rel);
        $comunidades = $this->detectarComunidades($alumnos, $rel);
        $roles = $this->calcularRoles($centralidades, $comunidades);
        $reciprocidad = $this->matrizReciprocidadDesdeRelaciones($alumnos, $rel);

        $result = array_merge($metricas, [
            'sociograma' => $grafo,
            'preferencias' => [
                'labels' => $prefGraf['labels'],
                'data' => $prefGraf['data'],
            ],
            'rechazos' => [
                'labels' => $rechGraf['labels'],
                'data' => $rechGraf['data'],
            ],
            'communities' => $comunidades,
            'centralities' => $centralidades,
            'roles' => $roles,
            'reciprocidad' => $reciprocidad,
        ]);

        Log::info('AnalisisGrupal: calculo completado', [
            'grupo_id' => $grupoId,
            'asignacion_test_id' => $asignacionTestId,
            'alumnos' => $alumnos->count(),
            'relaciones' => $result['totales']['relaciones'] ?? 0,
            'duracion_ms' => round((microtime(true) - $start) * 1000),
        ]);

        return $result;
    }

    public function cacheKey(int $grupoId, int $asignacionTestId): string
    {
        return "analisis-grupal:grupo:{$grupoId}:asignacion:{$asignacionTestId}";
    }

    private function obtenerAlumnosGrupo(int $grupoId): Collection
    {
        return Estudiante::whereIn('id', function ($q) use ($grupoId) {
            $q->select('id_estudiante')
                ->from('estudiantes_grupos')
                ->where('id_grupo', $grupoId);
        })
            ->orderBy('nombre')
            ->get();
    }

    private function prepararSerieBarras(Collection $alumnos, Collection $conteo): array
    {
        $labels = [];
        $data = [];

        $conteo
            ->filter(fn ($v) => (int) $v > 0)
            ->sortDesc()
            ->each(function ($value, $studentId) use ($alumnos, &$labels, &$data) {
                $nombre = optional($alumnos->firstWhere('id', (int) $studentId))->nombre;
                if ($nombre) {
                    $labels[] = $nombre;
                    $data[] = (int) $value;
                }
            });

        return compact('labels', 'data');
    }

    private function calcularMetricasRed(Collection $alumnos, Collection $rel): array
    {
        $n = $alumnos->count();
        $m = $rel->count();
        $pref = $rel->where('tipo_relacion', 'preferido')->values();
        $rech = $rel->where('tipo_relacion', 'rechazado')->values();

        $respondieron = $rel->pluck('alumno_a_id')->unique()->count();
        $participacion = $n > 0 ? round($respondieron / $n, 4) : 0.0;
        $density = $n > 1 ? round($m / ($n * ($n - 1)), 4) : 0.0;

        $prefKeys = $pref->map(fn ($r) => $r->alumno_a_id . '-' . $r->alumno_b_id)->flip();
        $reciprocalPrefEdges = 0;
        foreach ($pref as $r) {
            if ($prefKeys->has($r->alumno_b_id . '-' . $r->alumno_a_id)) {
                $reciprocalPrefEdges++;
            }
        }
        $reciprocity = $pref->count() > 0 ? round($reciprocalPrefEdges / $pref->count(), 4) : 0.0;

        $polarization = $m > 0 ? round(abs($pref->count() - $rech->count()) / $m, 4) : 0.0;

        return [
            'participation_rate' => $participacion,
            'density' => $density,
            'polarization' => $polarization,
            'reciprocity' => $reciprocity,
            'totales' => [
                'alumnos' => $n,
                'relaciones' => $m,
                'preferencias' => $pref->count(),
                'rechazos' => $rech->count(),
                'respondieron' => $respondieron,
            ],
        ];
    }

    private function detectarComunidades(Collection $alumnos, Collection $rel): array
    {
        $idToName = $alumnos->pluck('nombre', 'id');
        $adj = [];
        foreach ($alumnos as $a) {
            $adj[(int) $a->id] = [];
        }

        foreach ($rel->where('tipo_relacion', 'preferido') as $r) {
            $a = (int) $r->alumno_a_id;
            $b = (int) $r->alumno_b_id;
            $adj[$a][$b] = true;
            $adj[$b][$a] = true;
        }

        $visited = [];
        $communities = [];

        foreach (array_keys($adj) as $start) {
            if (isset($visited[$start]) || empty($adj[$start])) {
                continue;
            }

            $queue = [$start];
            $visited[$start] = true;
            $component = [];

            while ($queue) {
                $node = array_shift($queue);
                $component[] = $node;
                foreach (array_keys($adj[$node]) as $nbr) {
                    if (!isset($visited[$nbr])) {
                        $visited[$nbr] = true;
                        $queue[] = $nbr;
                    }
                }
            }

            if (count($component) > 1) {
                usort($component, fn ($x, $y) => strcmp((string) $idToName[$x], (string) $idToName[$y]));
                $communities[] = array_map(fn ($id) => (string) $idToName[$id], $component);
            }
        }

        usort($communities, fn ($a, $b) => count($b) <=> count($a));

        return $communities;
    }

    private function calcularCentralidades(Collection $alumnos, Collection $rel): array
    {
        $ids = $alumnos->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $idToName = $alumnos->pluck('nombre', 'id');
        $idSet = array_fill_keys($ids, true);

        $inDegree = array_fill_keys($ids, 0);
        $outDegree = array_fill_keys($ids, 0);
        $preferenceInDegree = array_fill_keys($ids, 0);
        $preferenceOutDegree = array_fill_keys($ids, 0);
        $rejectionInDegree = array_fill_keys($ids, 0);
        $rejectionOutDegree = array_fill_keys($ids, 0);
        $adjUndirected = array_fill_keys($ids, []);

        foreach ($rel as $r) {
            $a = (int) $r->alumno_a_id;
            $b = (int) $r->alumno_b_id;
            if (!isset($idSet[$a], $idSet[$b]) || $a === $b) {
                continue;
            }

            $outDegree[$a]++;
            $inDegree[$b]++;

            if ($r->tipo_relacion === 'preferido') {
                $preferenceOutDegree[$a]++;
                $preferenceInDegree[$b]++;
                $adjUndirected[$a][$b] = true;
                $adjUndirected[$b][$a] = true;
            }

            if ($r->tipo_relacion === 'rechazado') {
                $rejectionOutDegree[$a]++;
                $rejectionInDegree[$b]++;
            }
        }

        $betweenness = $this->brandesBetweennessUndirected($ids, $adjUndirected);
        $closeness = $this->closenessUndirected($ids, $adjUndirected);

        $rows = [];
        foreach ($ids as $id) {
            $rows[] = [
                'id' => $id,
                'name' => (string) ($idToName[$id] ?? ('Alumno #' . $id)),
                'inDegree' => (int) ($inDegree[$id] ?? 0),
                'outDegree' => (int) ($outDegree[$id] ?? 0),
                'preferenceInDegree' => (int) ($preferenceInDegree[$id] ?? 0),
                'preferenceOutDegree' => (int) ($preferenceOutDegree[$id] ?? 0),
                'rejectionInDegree' => (int) ($rejectionInDegree[$id] ?? 0),
                'rejectionOutDegree' => (int) ($rejectionOutDegree[$id] ?? 0),
                'betweenness' => round((float) ($betweenness[$id] ?? 0), 4),
                'closeness' => round((float) ($closeness[$id] ?? 0), 4),
            ];
        }

        usort($rows, function ($a, $b) {
            return [$b['inDegree'], $b['betweenness'], $b['outDegree'], $a['name']]
                <=> [$a['inDegree'], $a['betweenness'], $a['outDegree'], $b['name']];
        });

        return $rows;
    }

    private function brandesBetweennessUndirected(array $ids, array $adj): array
    {
        $cb = array_fill_keys($ids, 0.0);

        foreach ($ids as $s) {
            $stack = [];
            $pred = array_fill_keys($ids, []);
            $sigma = array_fill_keys($ids, 0.0);
            $dist = array_fill_keys($ids, -1);

            $sigma[$s] = 1.0;
            $dist[$s] = 0;
            $queue = [$s];

            while ($queue) {
                $v = array_shift($queue);
                $stack[] = $v;
                foreach (array_keys($adj[$v] ?? []) as $w) {
                    if ($dist[$w] < 0) {
                        $queue[] = $w;
                        $dist[$w] = $dist[$v] + 1;
                    }
                    if ($dist[$w] === $dist[$v] + 1) {
                        $sigma[$w] += $sigma[$v];
                        $pred[$w][] = $v;
                    }
                }
            }

            $delta = array_fill_keys($ids, 0.0);
            while ($stack) {
                $w = array_pop($stack);
                foreach ($pred[$w] as $v) {
                    if ($sigma[$w] > 0) {
                        $delta[$v] += ($sigma[$v] / $sigma[$w]) * (1 + $delta[$w]);
                    }
                }
                if ($w !== $s) {
                    $cb[$w] += $delta[$w];
                }
            }
        }

        foreach ($cb as $id => $value) {
            $cb[$id] = $value / 2.0;
        }

        return $cb;
    }

    private function closenessUndirected(array $ids, array $adj): array
    {
        $result = array_fill_keys($ids, 0.0);

        foreach ($ids as $start) {
            $dist = array_fill_keys($ids, -1);
            $dist[$start] = 0;
            $queue = [$start];

            while ($queue) {
                $node = array_shift($queue);
                foreach (array_keys($adj[$node] ?? []) as $nbr) {
                    if ($dist[$nbr] !== -1) {
                        continue;
                    }
                    $dist[$nbr] = $dist[$node] + 1;
                    $queue[] = $nbr;
                }
            }

            $reachable = 0;
            $distanceSum = 0;
            foreach ($dist as $id => $d) {
                if ($id === $start || $d <= 0) {
                    continue;
                }
                $reachable++;
                $distanceSum += $d;
            }

            $result[$start] = ($reachable > 0 && $distanceSum > 0)
                ? $reachable / $distanceSum
                : 0.0;
        }

        return $result;
    }

    private function calcularRoles(array $centralidades, array $comunidades): array
    {
        $leaders = collect($centralidades)
            ->filter(fn ($c) => ($c['preferenceInDegree'] ?? 0) > 0)
            ->sortByDesc('preferenceInDegree')
            ->take(3)
            ->pluck('name')
            ->values()
            ->all();

        $puentes = collect($centralidades)
            ->filter(fn ($c) => ($c['betweenness'] ?? 0) > 0)
            ->sortByDesc('betweenness')
            ->take(3)
            ->pluck('name')
            ->values()
            ->all();

        $aislados = collect($centralidades)
            ->filter(fn ($c) => (($c['inDegree'] ?? 0) + ($c['outDegree'] ?? 0)) === 0)
            ->pluck('name')
            ->values()
            ->all();

        $cohesivos = [];
        if (!empty($comunidades)) {
            $largest = collect($comunidades)->sortByDesc(fn ($c) => count($c))->first();
            if (is_array($largest) && count($largest) >= 3) {
                $cohesivos = array_values($largest);
            }
        }

        return compact('leaders', 'puentes', 'aislados', 'cohesivos');
    }

    private function matrizReciprocidadDesdeRelaciones(Collection $alumnos, Collection $rel): array
    {
        $labels = $alumnos->pluck('nombre')->values()->toArray();
        $indexOf = $alumnos->pluck('id')->map(fn ($v) => (int) $v)->flip();
        $n = count($labels);

        $M = array_fill(0, $n, array_fill(0, $n, 0));

        $dir = [];
        foreach ($rel as $r) {
            $i = $indexOf[(int) $r->alumno_a_id] ?? null;
            $j = $indexOf[(int) $r->alumno_b_id] ?? null;
            if ($i === null || $j === null || $i === $j) {
                continue;
            }
            $dir["$i-$j"] = $r->tipo_relacion;
        }

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if ($i === $j) {
                    continue;
                }

                $AB = $dir["$i-$j"] ?? null;
                $BA = $dir["$j-$i"] ?? null;

                $M[$i][$j] = match (true) {
                    $AB === 'preferido' && $BA === 'preferido' => 4,
                    $AB === 'rechazado' && $BA === 'rechazado' => 3,
                    ($AB === 'preferido' && $BA === 'rechazado') || ($AB === 'rechazado' && $BA === 'preferido') => 5,
                    $AB === 'preferido' || $BA === 'preferido' => 2,
                    $AB === 'rechazado' || $BA === 'rechazado' => 1,
                    default => 0,
                };
            }
        }

        $data = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $data[] = ['x' => $j, 'y' => $i, 'v' => $M[$i][$j]];
            }
        }

        return compact('labels', 'data');
    }
}
