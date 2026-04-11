<?php

namespace App\Livewire;

use App\Models\AsignacionTest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class AssignedTests extends Component
{
    public Collection $asignaciones;

    public ?AsignacionTest $asignacionSeleccionada = null;

    public ?string $resultado = null;

    public string $resultadoTipo = 'success';

    protected $listeners = [
        'refreshAssignedTests' => 'loadAsignaciones',
        'resetAsignacion' => 'resetModal',
    ];

    public function mount(): void
    {
        $this->asignaciones = collect();
        $this->loadAsignaciones();
    }

    public function loadAsignaciones(): void
    {
        $this->asignaciones = $this->baseQuery()
            ->get()
            ->map(fn (AsignacionTest $asignacion) => $this->decorateAssignment($asignacion));

        if ($this->asignacionSeleccionada) {
            $this->asignacionSeleccionada = $this->asignaciones
                ->firstWhere('id', $this->asignacionSeleccionada->id);
        }
    }

    public function seleccionarAsignacion(int $id): void
    {
        $asignacion = $this->baseQuery()
            ->whereKey($id)
            ->first();

        if (! $asignacion) {
            $this->setResult(__('Assignment not found.'), 'warning');
            return;
        }

        $this->asignacionSeleccionada = $this->decorateAssignment($asignacion);
    }

    public function regenerarClave(int $id): void
    {
        $asignacion = $this->findAssignment($id);

        if (! $asignacion) {
            $this->setResult(__('You do not have permission to update this assignment.'), 'danger');
            return;
        }

        if ($asignacion->estado === 'aplicado') {
            $this->setResult(__('You cannot regenerate the key for a closed assignment.'), 'warning');
            return;
        }

        $asignacion->update([
            'clave_acceso' => $this->generateAccessKey(),
        ]);

        $this->setResult(__('A new access key was generated for the test.'), 'success');
        $this->refreshState($id);
    }

    public function cerrarAsignacion(int $id): void
    {
        $asignacion = $this->findAssignment($id);

        if (! $asignacion) {
            $this->setResult(__('You do not have permission to close this assignment.'), 'danger');
            return;
        }

        if ($asignacion->estado === 'aplicado') {
            $this->setResult(__('The assignment was already closed.'), 'info');
            return;
        }

        $asignacion->update(['estado' => 'aplicado']);

        $this->setResult(__('The assignment is closed and will no longer accept new responses.'), 'success');
        $this->refreshState($id);
    }

    public function reabrirAsignacion(int $id): void
    {
        $asignacion = $this->findAssignment($id);

        if (! $asignacion) {
            $this->setResult(__('You do not have permission to reopen this assignment.'), 'danger');
            return;
        }

        [$respondieron, $total] = $this->calculateProgress($asignacion);

        if ($total > 0 && $respondieron >= $total) {
            $this->setResult(__('The assignment is already complete. No need to reopen it.'), 'info');
            return;
        }

        $nuevoEstado = $respondieron > 0 ? 'en progreso' : 'pendiente';
        $asignacion->update(['estado' => $nuevoEstado]);

        $this->setResult(__('The assignment is now available to students again.'), 'success');
        $this->refreshState($id);
    }

    public function resetModal(): void
    {
        $this->asignacionSeleccionada = null;
    }

    public function clearResult(): void
    {
        $this->resultado = null;
        $this->resultadoTipo = 'success';
    }

    public function render()
    {
        return view('livewire.assigned-tests');
    }

    private function baseQuery(): Builder
    {
        return AsignacionTest::query()
            ->with([
                'grupo.estudiantes' => fn ($query) => $query->orderBy('nombre'),
                'test',
            ])
            ->where('profesor_id', Auth::id())
            ->latest();
    }

    private function findAssignment(int $id): ?AsignacionTest
    {
        return AsignacionTest::query()
            ->whereKey($id)
            ->where('profesor_id', Auth::id())
            ->first();
    }

    private function decorateAssignment(AsignacionTest $asignacion): AsignacionTest
    {
        [$respondieron, $total, $pendingStudents] = $this->calculateProgress($asignacion, true);

        $asignacion->progreso_respondieron = $respondieron;
        $asignacion->progreso_total = $total;
        $asignacion->progreso_pct = $total > 0 ? round($respondieron / $total * 100) : 0;
        $asignacion->alumnos_pendientes = $pendingStudents;
        $asignacion->alumnos_pendientes_count = $pendingStudents->count();
        $asignacion->entry_url = route('test.ingresar');
        $asignacion->share_text = "Enlace: {$asignacion->entry_url}\nClave: {$asignacion->clave_acceso}";
        $asignacion->can_export_pdf = $respondieron > 0;
        $asignacion->can_close = $asignacion->estado !== 'aplicado';
        $asignacion->can_reopen = $asignacion->estado === 'aplicado' && $respondieron < $total;

        return $asignacion;
    }

    private function calculateProgress(AsignacionTest $asignacion, bool $withPendingStudents = false): array
    {
        $studentIds = $asignacion->grupo?->estudiantes
            ?->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all() ?? [];

        $respondedIds = $asignacion->respuestas()
            ->distinct('alumno_id')
            ->pluck('alumno_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $respondieron = count($respondedIds);
        $total = count($studentIds);

        if (! $withPendingStudents) {
            return [$respondieron, $total];
        }

        $pendingStudents = $asignacion->grupo?->estudiantes
            ?->reject(fn ($student) => in_array((int) $student->id, $respondedIds, true))
            ->values() ?? collect();

        return [$respondieron, $total, $pendingStudents];
    }

    private function refreshState(int $id): void
    {
        $this->loadAsignaciones();
        $this->seleccionarAsignacion($id);
    }

    private function generateAccessKey(): string
    {
        do {
            $key = Str::upper(Str::random(8));
        } while (
            AsignacionTest::query()
                ->where('clave_acceso', $key)
                ->exists()
        );

        return $key;
    }

    private function setResult(string $message, string $type = 'success'): void
    {
        $this->resultado = $message;
        $this->resultadoTipo = $type;
    }
}
