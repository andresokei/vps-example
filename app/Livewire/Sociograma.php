<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AsignacionTest;
use App\Models\Relacion;
use App\Models\Respuesta;


class Sociograma extends Component
{
    public $asignacionTestId;
    public $nodes = [];
    public $links = [];
    public $selectedNode = '';

    public function mount($asignacionTestId)
    {
        $this->asignacionTestId = $asignacionTestId;
        $this->loadSociogramData();
    }

    public function updatedSelectedNode()
    {
        $this->loadSociogramData();
        $this->emitSociogramData();
    }

    private function emitSociogramData()
{
    $filteredLinks = collect($this->links);

    if ($this->selectedNode) {
        $filteredLinks = $filteredLinks->filter(function ($link) {
            return $link['from'] == $this->selectedNode || $link['to'] == $this->selectedNode;
        });
    }

    $this->dispatch('rerenderSociogram',
        nodes: $this->nodes,
        links: $filteredLinks->values()->toArray()
    );
}

    

    public function loadSociogramData()
{
    try {
        $asignacionTest = AsignacionTest::with('grupo.estudiantes')->findOrFail($this->asignacionTestId);
        
        // Debugging: Verifica si se ha cargado correctamente
        // dd($asignacionTest);
        // dd($asignacionTest->grupo->estudiantes);

        // Continuar con la carga de nodos y enlaces...
    } catch (\Exception $e) {
        logger('Error loading sociogram data: ' . $e->getMessage());
        session()->flash('error', __('Error loading sociogram data.'));
    }
}


    public function render()
    {
        return view('livewire.sociograma');
    }
}
