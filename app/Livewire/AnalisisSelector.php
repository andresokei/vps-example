<?php

namespace App\Livewire;

use App\Models\AsignacionTest;
use App\Models\Grupo;
use App\Models\Relacion;
use App\Models\Respuesta;
use App\Services\AnalisisGrupalService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AnalisisSelector extends Component
{
    public $grupos;
    public $grupoSeleccionado = '';

    public $asignaciones = [];
    public $asignacionTestId = null;

    public array $analisis = [];
    public string $resultadoAnalisis = '';
    public string $resultadoTipo = 'info';
    public $updateTrigger = null;

    public function mount(): void
    {
        $this->grupos = Grupo::where('id_profesor', Auth::id())
            ->orderBy('nombre_grupo')
            ->get();
    }

    public function updatedGrupoSeleccionado(): void
    {
        $this->resetEstadoAnalisis();

        if (!$this->grupoSeleccionado) {
            return;
        }

        $this->asignaciones = AsignacionTest::with('test:id,nombre_test')
            ->withCount('respuestas')
            ->where('grupo_id', $this->grupoSeleccionado)
            ->where('profesor_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        if ($this->asignaciones->isEmpty()) {
            $this->resultadoTipo = 'info';
            $this->resultadoAnalisis = 'No se encontraron asignaciones de test para este grupo.';
            return;
        }

        $this->asignacionTestId = $this->asignaciones
            ->firstWhere(fn ($a) => (int) ($a->respuestas_count ?? 0) > 0)
            ?->id ?? $this->asignaciones->first()->id;
    }

    public function updatedAsignacionTestId(): void
    {
        $this->reset(['analisis']);
        $this->resultadoAnalisis = '';
        $this->resultadoTipo = 'info';
    }

    public function procesarAnalisis(AnalisisGrupalService $analisisGrupalService): void
    {
        if (!$this->grupoSeleccionado || !$this->asignacionTestId) {
            $this->resultadoTipo = 'warning';
            $this->resultadoAnalisis = 'Seleccione un grupo y una asignacion.';
            return;
        }

        $asignacionValida = AsignacionTest::whereKey($this->asignacionTestId)
            ->where('grupo_id', $this->grupoSeleccionado)
            ->where('profesor_id', Auth::id())
            ->exists();

        if (!$asignacionValida) {
            $this->resultadoTipo = 'danger';
            $this->resultadoAnalisis = 'La asignacion seleccionada no es valida para este grupo.';
            return;
        }

        if (!Respuesta::where('asignacion_test_id', $this->asignacionTestId)->exists()) {
            $this->resultadoTipo = 'warning';
            $this->resultadoAnalisis = 'La asignacion seleccionada no tiene respuestas.';
            return;
        }

        $forzarRefresco = $this->relacionesDesactualizadas((int) $this->asignacionTestId);
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

    private function cargarResultado(array $full): void
    {
        $this->analisis = $full;
        $this->updateTrigger = now()->timestamp;

        $this->dispatch('actualizarSociograma', $full['sociograma']);
        $this->dispatch('actualizarGraficoPreferencias', $full['preferencias']);
        $this->dispatch('actualizarGraficoRechazos', $full['rechazos']);
        $this->dispatch('actualizarMatrizReciprocidad', $full['reciprocidad']);

        $this->resultadoTipo = 'success';
        $this->resultadoAnalisis = 'Analisis generado correctamente.';
    }

    private function resetEstadoAnalisis(): void
    {
        $this->reset(['asignacionTestId', 'asignaciones', 'resultadoAnalisis', 'analisis']);
        $this->resultadoTipo = 'info';
    }

    private function relacionesDesactualizadas(int $asignacionTestId): bool
    {
        $statsRel = Relacion::where('asignacion_test_id', $asignacionTestId)
            ->selectRaw('COUNT(*) as total, MAX(updated_at) as max_updated_at')
            ->first();

        if (!$statsRel || (int) ($statsRel->total ?? 0) === 0) {
            return true;
        }

        $statsResp = Respuesta::where('asignacion_test_id', $asignacionTestId)
            ->selectRaw('COUNT(*) as total, MAX(updated_at) as max_updated_at')
            ->first();

        if (!$statsResp || (int) ($statsResp->total ?? 0) === 0) {
            return false;
        }

        $respUpdatedAt = $statsResp->max_updated_at ? strtotime((string) $statsResp->max_updated_at) : 0;
        $relUpdatedAt = $statsRel->max_updated_at ? strtotime((string) $statsRel->max_updated_at) : 0;

        return $respUpdatedAt > $relUpdatedAt;
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
}
