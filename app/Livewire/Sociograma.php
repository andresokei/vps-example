<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Relacion;
use App\Models\Estudiante;

class Sociograma extends Component
{
    public $asignacionTestId;

    public function mount($asignacionTestId)
    {
        $this->asignacionTestId = $asignacionTestId;
    }

    public function render()
    {
        // Obtener las relaciones para un test específico
        $relaciones = Relacion::with(['alumnoA', 'alumnoB'])
            ->where('asignacion_test_id', $this->asignacionTestId)
            ->get();

        // Obtener todos los estudiantes (sin importar si tienen relaciones o no)
        $todosLosAlumnos = Estudiante::all();

        // Pasar los datos a la vista
        return view('livewire.sociograma', [
            'relaciones' => $relaciones,
            'todosLosAlumnos' => $todosLosAlumnos, // Pasamos todos los alumnos a la vista
        ]);
    }
}
