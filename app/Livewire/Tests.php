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
        'resetAsignacion' => 'resetModal'
    ];

    public function mount()
    {
        // Solo obtener grupos del profesor actual
        $this->grupos = Grupo::where('id_profesor', auth()->id())->get();
        
        // Los tests son globales, pero si tienen un campo de propiedad, deberías filtrarlos
        $this->tests = Test::all();
    }

    public function asignar()
    {
        $this->validate([
            'test_id' => 'required|exists:tests,id',
            'grupo_id' => 'required|exists:grupos,id',
        ]);

        // Verificar que el grupo pertenezca al profesor actual antes de asignar
        $grupo = Grupo::where('id', $this->grupo_id)
            ->where('id_profesor', auth()->id())
            ->first();

        if (!$grupo) {
            session()->flash('error', __('You do not have permission to assign tests to this group.'));
            return;
        }

        AsignacionTest::create([
            'test_id' => $this->test_id,
            'profesor_id' => auth()->id(),
            'grupo_id' => $this->grupo_id,
            'clave_acceso' => Str::random(10),
            'estado' => 'pendiente',
        ]);

        session()->flash('success', __('Test assigned successfully (legacy).'));
        $this->reset(['test_id', 'grupo_id']);
    }

    public function actualizarGrupos()
    {
        // Solo obtener grupos del profesor actual
        $this->grupos = Grupo::where('id_profesor', auth()->id())->get();
    }

    public function seleccionarAsignacion($asignacionId)
    {
        // Verificar que la asignación pertenezca al profesor actual
        $this->asignacionSeleccionada = AsignacionTest::with(['test', 'grupo'])
            ->where('id', $asignacionId)
            ->where(function($query) {
                $query->where('profesor_id', auth()->id())
                    ->orWhereHas('grupo', function($groupQuery) {
                        $groupQuery->where('id_profesor', auth()->id());
                    });
            })
            ->first();
    }

    public function resetModal()
    {
        $this->asignacionSeleccionada = null;
    }

    public function render()
    {
        // Solo obtener asignaciones del profesor actual
        $asignaciones = AsignacionTest::with(['test', 'grupo'])
            ->where(function($query) {
                $query->where('profesor_id', auth()->id())
                    ->orWhereHas('grupo', function($groupQuery) {
                        $groupQuery->where('id_profesor', auth()->id());
                    });
            })
            ->get();

        return view('livewire.tests', [
            'asignaciones' => $asignaciones,
        ]);
    }
}