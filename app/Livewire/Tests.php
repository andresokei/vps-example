<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AsignacionTest;
use App\Models\Test;
use App\Models\Grupo;
use Illuminate\Support\Str;

class Tests extends Component
{
    public $test_id;
    public $grupo_id;
    public $grupos;
    public $tests;
    public $asignacionSeleccionada;

    protected $listeners = [
        'grupoCreado' => 'actualizarGrupos',
        'grupoEliminado' => 'actualizarGrupos',
        'modalClosed' => 'resetModal'
    ];

    public function mount()
    {
        $this->grupos = Grupo::all();
        $this->tests = Test::all();
    }

    public function asignar()
    {
        $this->validate([
            'test_id' => 'required|exists:tests,id',
            'grupo_id' => 'required|exists:grupos,id',
        ]);

        AsignacionTest::create([
            'test_id' => $this->test_id,
            'profesor_id' => auth()->id(),
            'grupo_id' => $this->grupo_id,
            'clave_acceso' => Str::random(10),
            'estado' => 'pendiente',
        ]);

        session()->flash('success', 'Test asignado con éxito.');
        $this->reset(['test_id', 'grupo_id']);
    }

    public function actualizarGrupos()
    {
        $this->grupos = Grupo::all();
    }

   public function verDetalles($asignacionId)
{
    $asignacion = AsignacionTest::with('test', 'grupo')->find($asignacionId);
    
    // Guarda la asignación seleccionada en la propiedad correcta
    $this->asignacionSeleccionada = $asignacion;

    // Verifica si la asignación fue encontrada
    if ($this->asignacionSeleccionada) {
        // Emite un evento para abrir el modal
        $this->dispatch('mostrar-modal');
    } else {
        logger('No se encontró la asignación con ID: ' . $asignacionId);
    }
}


    

    

    public function resetModal()
    {
        $this->asignacionSeleccionada = null;
        logger('Modal cerrado y datos reiniciados');
    }

    public function render()
    {
        return view('livewire.tests', [
            'asignaciones' => AsignacionTest::with(['test', 'grupo'])->get(),
        ]);
    }
}
