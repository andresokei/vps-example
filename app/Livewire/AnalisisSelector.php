<?php

namespace App\Livewire;

use App\Models\AsignacionTest;
use App\Models\Grupo;
use App\Models\Relacion;
use App\Models\Respuesta;
use App\Services\AnalisisGrupalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Livewire\Component;

class AnalisisSelector extends Component
{
    public Collection $grupos;

    public string $grupoSeleccionado = '';

    public Collection $asignaciones;

    public $asignacionTestId = null;

    public array $analisis = [];

    public string $resultadoAnalisis = '';

    public string $resultadoTipo = 'info';

    public $updateTrigger = null;

    protected $queryString = [
        'grupoSeleccionado' => ['as' => 'grupo', 'except' => ''],
        'asignacionTestId' => ['as' => 'asignacion', 'except' => null],
    ];

    public function mount($grupoInicial = null, $asignacionInicial = null): void
    {
        $this->grupos = Grupo::query()
            ->where('id_profesor', Auth::id())
            ->orderBy('nombre_grupo')
            ->get();

        $this->asignaciones = collect();

        $this->hydrateInitialSelection($grupoInicial, $asignacionInicial);
    }

    public function hydrate(): void
    {
        // Livewire restores Eloquent collections by ID, without withCount attributes.
        if ($this->grupoSeleccionado !== '') {
            $this->loadAssignmentsForSelectedGroup();
        }
    }

    public function updatedGrupoSeleccionado(): void
    {
        $this->asignacionTestId = null;
        $this->asignaciones = collect();
        $this->analisis = [];
        $this->resultadoAnalisis = '';
        $this->resultadoTipo = 'info';

        if (! $this->grupoSeleccionado) {
            return;
        }

        $this->seleccionarAsignacionInicial();
    }

    public function updatedAsignacionTestId(): void
    {
        $this->reset(['analisis']);
        $this->resultadoAnalisis = '';
        $this->resultadoTipo = 'info';
        if ($this->asignaciones->firstWhere('id', $this->asignacionTestId)?->respuestas_count > 0) {
            $this->procesarAnalisis(app(AnalisisGrupalService::class));
        }
    }

    public function procesarAnalisis(AnalisisGrupalService $analisisGrupalService): void
    {
        if (! $this->grupoSeleccionado || ! $this->asignacionTestId) {
            $this->resultadoTipo = 'warning';
            $this->resultadoAnalisis = __('Select a group and an assignment.');
            return;
        }

        $asignacionValida = AsignacionTest::query()
            ->whereKey($this->asignacionTestId)
            ->where('grupo_id', $this->grupoSeleccionado)
            ->where('profesor_id', Auth::id())
            ->exists();

        if (! $asignacionValida) {
            $this->resultadoTipo = 'danger';
            $this->resultadoAnalisis = __('The selected assignment is not valid for this group.');
            return;
        }

        if (! Respuesta::query()->where('asignacion_test_id', $this->asignacionTestId)->exists()) {
            $this->resultadoTipo = 'warning';
            $this->resultadoAnalisis = __('The selected assignment has no responses.');
            return;
        }

        $readOnlyPreview = session()->has('impersonator_id');
        $relacionesDesactualizadas = $this->relacionesDesactualizadas((int) $this->asignacionTestId);

        if ($readOnlyPreview && $relacionesDesactualizadas) {
            $this->resultadoTipo = 'warning';
            $this->resultadoAnalisis = __('This analysis needs to be regenerated before it can be previewed in read-only admin mode.');
            return;
        }

        $forzarRefresco = $relacionesDesactualizadas;
        if ($forzarRefresco) {
            Relacion::generarDesdeRespuestas((int) $this->asignacionTestId);
        }

        $full = $analisisGrupalService->generar(
            (int) $this->grupoSeleccionado,
            (int) $this->asignacionTestId,
            $forzarRefresco
        );

        $this->cargarResultado($full);
    }

    public function render()
    {
        return view('livewire.analisis-selector', [
            'actual' => $this->asignaciones->firstWhere('id', $this->asignacionTestId),
            'grupos' => $this->grupos,
            'asignaciones' => $this->asignaciones,
            'resultadoAnalisis' => $this->resultadoAnalisis,
            'resultadoTipo' => $this->resultadoTipo,
            'analisis' => $this->analisis,
        ]);
    }

    private function hydrateInitialSelection($grupoInicial, $asignacionInicial): void
    {
        $groupId = $grupoInicial;
        if (! $groupId && $asignacionInicial) {
            $groupId = AsignacionTest::where('profesor_id', Auth::id())
                ->whereKey($asignacionInicial)->value('grupo_id');
        }

        if (! $groupId || ! $this->grupos->contains('id', $groupId)) {
            return;
        }

        $this->grupoSeleccionado = (string) $groupId;
        $this->seleccionarAsignacionInicial($asignacionInicial);
    }

    private function seleccionarAsignacionInicial(?int $solicitada = null): void
    {
        $this->loadAssignmentsForSelectedGroup();

        if ($solicitada !== null) {
            $this->asignacionTestId = $this->asignaciones->firstWhere('id', $solicitada)?->id;
            if ($this->asignacionTestId === null) {
                $this->resultadoTipo = 'warning';
                $this->resultadoAnalisis = __('The selected assignment is not valid for this group.');
                return;
            }
        } else {
            $this->asignacionTestId = $this->asignaciones->count() === 1 ? $this->asignaciones->first()->id : null;
        }
        $this->updatedAsignacionTestId();
    }

    private function loadAssignmentsForSelectedGroup(): void
    {
        $this->asignaciones = AsignacionTest::query()
            ->with('test:id,nombre_test,nombre_test_en')
            ->with('grupo.estudiantes:id')
            ->withCount(['respuestas', 'respuestas as selecciones_count' => fn ($q) => $q->whereNotNull('respuesta')])
            ->addSelect(['participantes_count' => Respuesta::selectRaw('count(distinct alumno_id)')
                ->whereColumn('respuestas.asignacion_test_id', 'asignaciones_test.id')])
            ->where('grupo_id', $this->grupoSeleccionado)
            ->where('profesor_id', Auth::id())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    private function cargarResultado(array $full): void
    {
        $this->analisis = $full;
        $this->updateTrigger = now()->timestamp;

        $centralityById = collect($full['centralities'])->keyBy('id')->toArray();

        $enrichedNodes = array_map(function (array $node) use ($centralityById) {
            $centrality = $centralityById[$node['id']] ?? null;

            $node['centrality'] = $centrality ? [
                'inDegree' => $centrality['inDegree'],
                'outDegree' => $centrality['outDegree'],
                'betweenness' => $centrality['betweenness'],
                'closeness' => $centrality['closeness'],
            ] : null;

            return $node;
        }, $full['sociograma']['nodes']);

        $enrichedSociograma = $full['sociograma'];
        $enrichedSociograma['nodes'] = $enrichedNodes;
        $enrichedSociograma['roles'] = $full['roles'] ?? [];

        $this->dispatch('actualizarSociograma', $enrichedSociograma);
        $this->dispatch('actualizarGraficoPreferencias', $full['preferencias']);
        $this->dispatch('actualizarGraficoRechazos', $full['rechazos']);
    }

    private function relacionesDesactualizadas(int $asignacionTestId): bool
    {
        $hasDuplicatedPairs = Relacion::query()
            ->where('asignacion_test_id', $asignacionTestId)
            ->selectRaw('alumno_a_id, alumno_b_id, tipo_relacion, COUNT(*) as total')
            ->groupBy('alumno_a_id', 'alumno_b_id', 'tipo_relacion')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicatedPairs) {
            return true;
        }

        $statsRel = Relacion::query()
            ->where('asignacion_test_id', $asignacionTestId)
            ->selectRaw('COUNT(*) as total, MAX(updated_at) as max_updated_at')
            ->first();

        if (! $statsRel || (int) ($statsRel->total ?? 0) === 0) {
            return true;
        }

        $statsResp = Respuesta::query()
            ->where('asignacion_test_id', $asignacionTestId)
            ->selectRaw('COUNT(*) as total, MAX(updated_at) as max_updated_at')
            ->first();

        if (! $statsResp || (int) ($statsResp->total ?? 0) === 0) {
            return false;
        }

        $respUpdatedAt = $statsResp->max_updated_at ? strtotime((string) $statsResp->max_updated_at) : 0;
        $relUpdatedAt = $statsRel->max_updated_at ? strtotime((string) $statsRel->max_updated_at) : 0;

        return $respUpdatedAt > $relUpdatedAt;
    }
}
