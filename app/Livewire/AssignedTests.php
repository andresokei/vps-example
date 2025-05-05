<?php
// app/Http/Livewire/AssignedTests.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AsignacionTest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;

class AssignedTests extends Component
{
    use AuthorizesRequests;

    public $asignaciones;
    public $asignacionSeleccionada;

    protected $listeners = [
        'refreshAssignedTests' => 'loadAsignaciones',
        'resetAsignacion'      => 'resetModal',
    ];

    /**
     * Validación para seleccionar una asignación.
     * Aquí prevenimos IDs inválidos o que no pertenezcan al usuario.
     */
    protected function rules()
    {
        return [
            'asignacionSeleccionada.id' => [
                'required',
                'integer',
                Rule::exists('asignaciones_test', 'id')
                    ->where('profesor_id', Auth::id()),
            ],
        ];
    }

    public function mount()
    {
        $this->loadAsignaciones();
    }

    public function loadAsignaciones()
    {
        // Solo cargamos las asignaciones del profesor autenticado
        $this->asignaciones = AsignacionTest::with(['grupo', 'test'])
            ->where('profesor_id', Auth::id())
            ->get();
    }

    /**
     * Reserva la asignación en la propiedad tras validarla y autorizarla.
     */
    public function seleccionarAsignacion($id)
{
    // Obtenemos el modelo directamente sin validación previa
    $asignacion = AsignacionTest::with(['grupo', 'test'])
        ->where('id', $id)
        ->where('profesor_id', Auth::id())
        ->first();
        
    if ($asignacion) {
        $this->asignacionSeleccionada = $asignacion;
    } else {
        // Log para depuración
        \Log::error('No se encontró la asignación con ID: ' . $id);
    }
}

    public function resetModal()
    {
        $this->asignacionSeleccionada = null;
    }

    public function render()
    {
        return view('livewire.assigned-tests');
    }
}
