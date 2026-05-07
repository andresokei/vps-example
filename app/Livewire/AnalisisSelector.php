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

    public function updatedGrupoSeleccionado(): void
    {
        $this->resetEstadoAnalisis();

        if (! $this->grupoSeleccionado) {
            return;
        }

        $this->loadAssignmentsForSelectedGroup();

        if ($this->asignaciones->isEmpty()) {
            $this->resultadoTipo = 'info';
            $this->resultadoAnalisis = __('No test assignments found for this group.');
            return;
        }

        $this->asignacionTestId = $this->defaultAssignmentId();
    }

    public function updatedAsignacionTestId(): void
    {
        $this->reset(['analisis']);
        $this->resultadoAnalisis = '';
        $this->resultadoTipo = 'info';
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
            'grupos' => $this->grupos,
            'asignaciones' => $this->asignaciones,
            'resultadoAnalisis' => $this->resultadoAnalisis,
            'resultadoTipo' => $this->resultadoTipo,
            'analisis' => $this->analisis,
        ]);
    }

    private function hydrateInitialSelection($grupoInicial, $asignacionInicial): void
    {
        $assignment = null;

        if ($asignacionInicial) {
            $assignment = AsignacionTest::query()
                ->with('test:id,nombre_test,nombre_test_en')
                ->withCount('respuestas')
                ->whereKey((int) $asignacionInicial)
                ->where('profesor_id', Auth::id())
                ->first();
        }

        $groupId = $assignment?->grupo_id ?? ($grupoInicial ? (int) $grupoInicial : null);

        if (! $groupId || ! $this->grupos->contains('id', $groupId)) {
            return;
        }

        $this->grupoSeleccionado = (string) $groupId;
        $this->loadAssignmentsForSelectedGroup();

        if ($this->asignaciones->isEmpty()) {
            return;
        }

        $selectedAssignment = $assignment
            && $this->asignaciones->contains('id', $assignment->id)
                ? $assignment
                : $this->asignaciones->firstWhere('id', $this->defaultAssignmentId());

        $this->asignacionTestId = $selectedAssignment?->id;

        if (($selectedAssignment->respuestas_count ?? 0) > 0) {
            $this->procesarAnalisis(app(AnalisisGrupalService::class));
        }
    }

    private function loadAssignmentsForSelectedGroup(): void
    {
        $this->asignaciones = AsignacionTest::query()
            ->with('test:id,nombre_test,nombre_test_en')
            ->withCount('respuestas')
            ->where('grupo_id', $this->grupoSeleccionado)
            ->where('profesor_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();
    }

    private function defaultAssignmentId(): ?int
    {
        return $this->asignaciones
            ->first(fn ($asignacion) => (int) ($asignacion->respuestas_count ?? 0) > 0)
            ?->id ?? $this->asignaciones->first()?->id;
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
        $this->dispatch('actualizarMatrizReciprocidad', $full['reciprocidad']);

        $this->resultadoTipo = 'success';
        $this->resultadoAnalisis = __('Analysis generated successfully.');
    }

    private function resetEstadoAnalisis(): void
    {
        $this->reset(['asignacionTestId', 'asignaciones', 'resultadoAnalisis', 'analisis']);
        $this->resultadoTipo = 'info';
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
