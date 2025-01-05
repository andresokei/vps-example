<?php
namespace App\Livewire;



use Livewire\Component;
use App\Models\Grupo;
use App\Models\AsignacionTest;
use Illuminate\Support\Facades\Auth;

class AnalisisSelector extends Component
{
    public $grupos;
    public $grupoSeleccionado;
    public $tipoAnalisis;
    public $resultadoAnalisis;
    public $asignacionTestId; // Agrega esta propiedad

    public function mount()
    {
        $this->grupos = Grupo::where('id_profesor', Auth::user()->id)->get();
        
        // Obtener asignacionTestId
        $asignacionTest = AsignacionTest::where('profesor_id', Auth::user()->id)->first();
        $this->asignacionTestId = $asignacionTest ? $asignacionTest->id : null;
    }

    public function procesarAnalisis()
    {
        // Lógica para procesar el análisis
        $this->resultadoAnalisis = "Análisis procesado para el grupo seleccionado";
    }

    public function render()
    {
        return view('livewire.analisis-selector');
    }
}
