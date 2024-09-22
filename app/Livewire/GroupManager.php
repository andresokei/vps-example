<?php

namespace App\Livewire;
use Illuminate\Support\Facades\DB;

use Livewire\Component;
use App\Models\Grupo; // Asegúrate de usar el modelo correcto
use App\Models\User;


class GroupManager extends Component
{
    public $groupName;
    public $groups;
    public $selectedGroup = null;
    public $studentId;
    public $students;
   


    public function createGroup()
    {
        Grupo::create([
            'nombre_grupo' => $this->groupName,
            'id_profesor' => auth()->user()->id, // O el valor que corresponda
        ]);
        $this->groupName = ''; // Limpiar el campo

        // Recargar los grupos para reflejar el nuevo en la vista
        $this->groups = Grupo::where('id_profesor', auth()->user()->id)->get();
    }



    public function mount()
    {
        $this->groups = Grupo::where('id_profesor', auth()->user()->id)->get();

        $this->students = User::where('rol', 'estudiante')->get();

    }

    public function selectGroup($groupId)
    {
        // Guardamos el grupo seleccionado
        $this->selectedGroup = $groupId;
    }

    public function addStudentToGroup()
    {
        // Verificar que se haya seleccionado un grupo y un alumno
        if ($this->selectedGroup && $this->studentId) {
            DB::table('estudiantes_grupos')->insert([
                'id_grupo' => $this->selectedGroup,
                'id_estudiante' => $this->studentId,
            ]);

            // Limpiar el campo después de añadir el alumno
            $this->studentId = null;

            session()->flash('message', 'El estudiante ha sido añadido al grupo.');
        }
    }
}
