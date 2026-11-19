<?php

namespace App\Livewire;

use App\Livewire\Concerns\ManagesGroupStudents;
use App\Models\Grupo;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GroupRoster extends Component
{
    use ManagesGroupStudents;

    public Grupo $grupo;

    public function mount(Grupo $grupo): void
    {
        abort_unless((int) $grupo->id_profesor === (int) Auth::id(), 404);
        $this->grupo = $grupo;
        $this->loadGroupStudents();
    }

    protected function rosterGroupId(): int
    {
        return $this->grupo->id;
    }

    public function render()
    {
        return view('livewire.group-roster');
    }
}
