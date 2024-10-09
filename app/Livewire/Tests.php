<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AsignacionesTest;
use App\Models\Test;
use App\Models\Grupo;
use Illuminate\Support\Str;

class Tests extends Component
{
    public $test_id;
    public $grupo_id;
    public $testSeleccionado;
    
    // Listeners para escuchar el evento de creación de grupos
    protected $listeners = ['grupoCreado' => 'actualizarGrupos','grupoEliminado' => 'actualizarGrupos'];

    // Método para asignar el test
    public function asignar()
    {
        $this->validate([
            'test_id' => 'required|exists:tests,id',
            'grupo_id' => 'required|exists:grupos,id,id_profesor,' . auth()->id(),  // Asegurar que el grupo pertenezca al profesor autenticado
        ]);

        AsignacionesTest::create([
            'test_id' => $this->test_id,
            'profesor_id' => auth()->id(),
            'grupo_id' => $this->grupo_id,
            'clave_acceso' => Str::random(10),
            'estado' => 'pendiente',
        ]);

        session()->flash('success', 'Test asignado con éxito.');
        $this->reset(); // Resetea los campos
    }

    // Método para actualizar los grupos al recibir el evento
    public function actualizarGrupos()
    {
        $this->grupos = Grupo::where('id_profesor', auth()->id())->get();  // Asegurar que solo se obtengan los grupos del profesor autenticado
    }

    // Método para ver detalles de una asignación
    public function verDetalles($asignacionId)
    {
        $asignacion = AsignacionesTest::where('id', $asignacionId)
                              ->where('profesor_id', auth()->id())  // Asegurar que la asignación pertenezca al profesor autenticado
                              ->with('test', 'grupo')
                              ->firstOrFail();
    
        // Guarda el test seleccionado en la propiedad
        $this->testSeleccionado = $asignacion;

        // Emite un evento para abrir el modal
        $this->dispatch('mostrar-modal');
    }

    public function render()
    {
        return view('livewire.tests', [
            'tests' => Test::all(),
            'grupos' => Grupo::where('id_profesor', auth()->id())->get(),  // Solo mostrar grupos del profesor autenticado
            'asignaciones' => AsignacionesTest::where('profesor_id', auth()->id())  // Filtrar asignaciones del profesor autenticado
                        ->with(['test', 'grupo'])
                        ->get(), 
        ]);
    }
}
