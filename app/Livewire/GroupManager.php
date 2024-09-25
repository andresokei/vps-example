<?php

namespace App\Livewire;
use App\Models\Estudiante;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Grupo;
use App\Models\User;

class GroupManager extends Component
{
    public $groupName;
    public $groups;
    public $selectedGroup = null;
    public $studentName = '';
    public $filteredStudents = [];

    // Método para crear un grupo
    public function createGroup()
    {
        Grupo::create([
            'nombre_grupo' => $this->groupName,
            'id_profesor' => auth()->user()->id,
        ]);

        $this->groupName = ''; // Limpiar el campo
        $this->groups = Grupo::where('id_profesor', auth()->user()->id)->get();  // Actualizar los grupos del profesor autenticado
    }

    // Método mount
    public function mount()
    {
        $this->groups = Grupo::where('id_profesor', auth()->user()->id)->get();  // Cargar solo los grupos del profesor autenticado
        $this->filteredStudents = [];
    }

    // Método para añadir un alumno a un grupo
    public function addStudentToGroup()
    {
        if ($this->selectedGroup && !empty($this->studentName)) {
            $student = DB::table('estudiantes')->where('nombre', $this->studentName)->first();

            if (!$student) {
                $studentId = DB::table('estudiantes')->insertGetId([
                    'nombre' => $this->studentName,
                    // 'id_profesor' => auth()->user()->id,  // Asociar el estudiante al profesor autenticado
                ]);
            } else {
                $studentId = $student->id;
            }

            // Insertar la relación entre el estudiante y el grupo
            DB::table('estudiantes_grupos')->insert([
                'id_grupo' => $this->selectedGroup,
                'id_estudiante' => $studentId,
            ]);

            $this->studentName = '';  // Limpiar el campo
            session()->flash('message', 'El estudiante ha sido añadido al grupo.');
        } else {
            session()->flash('error', 'Debes seleccionar un grupo y escribir un nombre de estudiante.');
        }
    }

    // Método para seleccionar un grupo
    public function selectGroup($groupId)
    {
        if ($this->selectedGroup === $groupId) {
            $this->selectedGroup = null;
        } else {
            $this->selectedGroup = $groupId;
        }
    }

    // Método para eliminar un grupo
    public function deleteGroup($groupId)
{
    $group = Grupo::where('id', $groupId)
                  ->where('id_profesor', auth()->user()->id)  // Verificar que el grupo pertenece al profesor autenticado
                  ->first();

    if ($group) {
        // Obtener todos los estudiantes relacionados con este grupo
        $studentsInGroup = DB::table('estudiantes_grupos')
                             ->where('id_grupo', $groupId)
                             ->pluck('id_estudiante');

        // Eliminar el grupo y las relaciones en estudiantes_grupos
        $group->delete();
        DB::table('estudiantes_grupos')->where('id_grupo', $groupId)->delete();

        // Verificar si cada estudiante está en otros grupos. Si no, eliminar el estudiante.
        foreach ($studentsInGroup as $studentId) {
            $isInOtherGroups = DB::table('estudiantes_grupos')
                                 ->where('id_estudiante', $studentId)
                                 ->exists();

            if (!$isInOtherGroups) {
                Estudiante::where('id', $studentId)->delete();
            }
        }

        // Actualizar los grupos del profesor autenticado
        $this->groups = Grupo::where('id_profesor', auth()->user()->id)->get();
        
        session()->flash('message', 'Grupo y estudiantes eliminados correctamente.');
    } else {
        session()->flash('error', 'No tienes permiso para eliminar este grupo.');
    }
}


    // Método para eliminar un estudiante de un grupo
    public function deleteStudentFromGroup($studentId, $groupId)
    {
        $group = Grupo::where('id', $groupId)
                      ->where('id_profesor', auth()->user()->id)  // Verificar que el grupo pertenece al profesor autenticado
                      ->first();

        if ($group) {
            // Eliminar al estudiante del grupo (tabla pivot)
            $group->students()->detach($studentId);

            // Comprobar si el estudiante pertenece a algún otro grupo
            $studentInOtherGroups = DB::table('estudiantes_grupos')->where('id_estudiante', $studentId)->exists();

            // Si el estudiante no pertenece a ningún otro grupo, lo eliminamos completamente
            if (!$studentInOtherGroups) {
                $student = Estudiante::find($studentId);
                if ($student) {
                    $student->delete();
                }
            }

            session()->flash('message', 'Estudiante eliminado del grupo con éxito.');
            $this->groups = Grupo::where('id_profesor', auth()->user()->id)->get();  // Actualizar los grupos del profesor autenticado
        } else {
            session()->flash('error', 'No tienes permiso para eliminar este estudiante.');
        }
    }

    public function render()
    {
        return view('livewire.group-manager');
    }
}
