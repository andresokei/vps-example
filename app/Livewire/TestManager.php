<?php

namespace App\Livewire;

use App\Models\AsignacionTest;
use App\Models\Pregunta;
use App\Models\Test;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TestManager extends Component
{
    public $tests = [];
    public $selectedTestId = null;
    public $preguntas = [];

    // Formulario de crear/editar test
    public $nombre = '';
    public $descripcion = '';
    public $editingTestId = null;

    // Formulario de añadir pregunta
    public $preguntaTexto = '';
    public $preguntaTipo = 'preferencia';

    public function mount(): void
    {
        $this->loadTests();
    }

    private function loadTests(): void
    {
        $this->tests = Test::where('id_profesor', Auth::id())
            ->withCount('preguntas')
            ->orderBy('nombre_test')
            ->get();
    }

    private function loadPreguntas(): void
    {
        if (! $this->selectedTestId) {
            $this->preguntas = [];
            return;
        }

        $this->preguntas = Pregunta::where('test_id', $this->selectedTestId)
            ->orderBy('orden')
            ->get()
            ->toArray();
    }

    // --- Tests CRUD ---

    public function abrirModalCrear(): void
    {
        $this->reset(['nombre', 'descripcion', 'editingTestId']);
        $this->resetErrorBag();
        $this->dispatch('openModalCrearTest');
    }

    public function crearTest(): void
    {
        $this->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tests', 'nombre_test')->where('id_profesor', Auth::id()),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ]);

        Test::create([
            'nombre_test' => $this->nombre,
            'descripcion' => $this->descripcion ?: null,
            'id_profesor' => Auth::id(),
        ]);

        $this->reset(['nombre', 'descripcion']);
        $this->dispatch('closeModalCrearTest');
        $this->loadTests();
        $this->dispatch('refreshAssignedTests');
        session()->flash('message', __('Test created successfully.'));
    }

    public function abrirModalEditar(int $testId): void
    {
        $test = Test::where('id', $testId)
            ->where('id_profesor', Auth::id())
            ->firstOrFail();

        $this->editingTestId = $test->id;
        $this->nombre = $test->nombre_test;
        $this->descripcion = $test->descripcion ?? '';
        $this->resetErrorBag();
        $this->dispatch('openModalEditarTest');
    }

    public function actualizarTest(): void
    {
        $test = Test::where('id', $this->editingTestId)
            ->where('id_profesor', Auth::id())
            ->firstOrFail();

        $this->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tests', 'nombre_test')
                    ->where('id_profesor', Auth::id())
                    ->ignore($test->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ]);

        $test->update([
            'nombre_test' => $this->nombre,
            'descripcion' => $this->descripcion ?: null,
        ]);

        $this->reset(['nombre', 'descripcion', 'editingTestId']);
        $this->dispatch('closeModalEditarTest');
        $this->loadTests();
        $this->dispatch('refreshAssignedTests');
        session()->flash('message', __('Test updated successfully.'));
    }

    public function eliminarTest(int $testId): void
    {
        $test = Test::where('id', $testId)
            ->where('id_profesor', Auth::id())
            ->firstOrFail();

        $tieneAsignacionesActivas = AsignacionTest::where('test_id', $testId)
            ->whereIn('estado', ['pendiente', 'en progreso'])
            ->exists();

        if ($tieneAsignacionesActivas) {
            session()->flash('error', __('Cannot delete a test with active assignments.'));
            return;
        }

        if ($this->selectedTestId === $testId) {
            $this->selectedTestId = null;
            $this->preguntas = [];
        }

        $test->delete();
        $this->loadTests();
        $this->dispatch('refreshAssignedTests');
        session()->flash('message', __('Test deleted successfully.'));
    }

    public function seleccionarTest(int $testId): void
    {
        Test::where('id', $testId)
            ->where('id_profesor', Auth::id())
            ->firstOrFail();

        $this->selectedTestId = $testId;
        $this->reset(['preguntaTexto', 'preguntaTipo']);
        $this->preguntaTipo = 'preferencia';
        $this->resetErrorBag();
        $this->loadPreguntas();
    }

    // --- Preguntas CRUD ---

    public function añadirPregunta(): void
    {
        $this->validate([
            'preguntaTexto' => ['required', 'string', 'max:500'],
            'preguntaTipo'  => ['required', 'in:preferencia,rechazo'],
        ]);

        Test::where('id', $this->selectedTestId)
            ->where('id_profesor', Auth::id())
            ->firstOrFail();

        $siguienteOrden = Pregunta::where('test_id', $this->selectedTestId)->max('orden') + 1;

        Pregunta::create([
            'test_id'        => $this->selectedTestId,
            'texto_pregunta' => $this->preguntaTexto,
            'tipo_pregunta'  => $this->preguntaTipo,
            'orden'          => $siguienteOrden,
        ]);

        $this->reset(['preguntaTexto']);
        $this->preguntaTipo = 'preferencia';
        $this->resetErrorBag('preguntaTexto');
        $this->loadPreguntas();
        $this->loadTests(); // actualiza el contador de preguntas
    }

    public function eliminarPregunta(int $preguntaId): void
    {
        $pregunta = Pregunta::where('id', $preguntaId)
            ->where('test_id', $this->selectedTestId)
            ->firstOrFail();

        // Verificar ownership a través del test
        Test::where('id', $this->selectedTestId)
            ->where('id_profesor', Auth::id())
            ->firstOrFail();

        $ordenEliminada = $pregunta->orden;
        $pregunta->delete();

        // Reordenar las siguientes
        Pregunta::where('test_id', $this->selectedTestId)
            ->where('orden', '>', $ordenEliminada)
            ->decrement('orden');

        $this->loadPreguntas();
        $this->loadTests();
    }

    public function moverPregunta(int $preguntaId, string $direccion): void
    {
        Test::where('id', $this->selectedTestId)
            ->where('id_profesor', Auth::id())
            ->firstOrFail();

        $pregunta = Pregunta::where('id', $preguntaId)
            ->where('test_id', $this->selectedTestId)
            ->firstOrFail();

        $vecina = Pregunta::where('test_id', $this->selectedTestId)
            ->when($direccion === 'up', fn ($q) => $q->where('orden', '<', $pregunta->orden)->orderByDesc('orden'))
            ->when($direccion === 'down', fn ($q) => $q->where('orden', '>', $pregunta->orden)->orderBy('orden'))
            ->first();

        if (! $vecina) {
            return;
        }

        [$pregunta->orden, $vecina->orden] = [$vecina->orden, $pregunta->orden];
        $pregunta->save();
        $vecina->save();

        $this->loadPreguntas();
    }

    public function render()
    {
        return view('livewire.test-manager');
    }
}
