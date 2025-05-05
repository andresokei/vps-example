<?php

namespace App\Livewire;

use App\Models\Estudiante;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Grupo;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GroupManager extends Component
{
    use WithFileUploads;

    public $groupName;
    public $groups;
    public $selectedGroup = null;
    public $studentNames = '';
    public $csvFile;
    public $groupStudents = []; 
    public $groupToDelete = null; // Nueva propiedad para el grupo a eliminar

    protected function rules()
    {
        return [
            'groupName'    => [
                'required',
                'string',
                'max:255',
                Rule::unique('grupos', 'nombre_grupo')
                    ->where('id_profesor', Auth::id()),
            ],
            'studentNames' => ['nullable', 'string', 'max:500', 'regex:/^[A-Za-zÀ-ÖØ-öø-ÿ ,]+$/'],
            'csvFile'      => ['nullable', 'file', 'mimes:csv,txt', 'max:2048'],
        ];
    }

    public function mount()
    {
        $this->loadGroups();
    }

    private function loadGroups()
    {
        $this->groups = Grupo::where('id_profesor', Auth::id())->get();
    }

    public function openModal($groupId)
    {
        // Autoriza que el grupo pertenezca al profesor
        if (Grupo::where('id', $groupId)->where('id_profesor', Auth::id())->exists()) {
            $this->selectedGroup = $groupId;
            $this->loadGroupStudents(); // Cargar estudiantes del grupo seleccionado
            $this->dispatch('openModal');
        } else {
            abort(403);
        }
    }

    // Método para cargar los estudiantes del grupo
    public function loadGroupStudents()
    {
        if ($this->selectedGroup) {
            $group = Grupo::find($this->selectedGroup);
            $this->groupStudents = $group ? $group->estudiantes : [];
        } else {
            $this->groupStudents = [];
        }
    }

    public function closeModal()
    {
        $this->dispatch('closeModal');
        $this->reset(['selectedGroup', 'studentNames', 'csvFile', 'groupStudents']);
    }

    public function createGroup()
    {
        $this->validateOnly('groupName');

        Grupo::create([
            'nombre_grupo' => $this->groupName,
            'id_profesor'  => Auth::id(),
        ]);

        session()->flash('message', 'Grupo creado correctamente.');
        $this->groupName = '';
        $this->loadGroups();
    }

    public function uploadCSV()
    {
        $this->validateOnly('csvFile');

        $path = $this->csvFile->getRealPath();
        $file = fopen($path, 'r');
        $rows = 0;
        $addedStudents = 0;

        while (($data = fgetcsv($file)) !== false && $rows < 1000) {
            if (!empty($data[0])) {
                $this->addStudentToGroup(trim($data[0]));
                $addedStudents++;
            }
            $rows++;
        }

        fclose($file);
        $this->reset('csvFile');
        
        // Recargar la lista de estudiantes
        $this->loadGroupStudents();
        
        session()->flash('message', $addedStudents . ' estudiantes añadidos desde CSV.');
    }

    public function addStudentFromList()
    {
        $this->validateOnly('studentNames');

        if ($this->selectedGroup) {
            $names = array_filter(array_map('trim', explode(',', $this->studentNames)));
            $addedStudents = 0;
            
            foreach ($names as $name) {
                $this->addStudentToGroup($name);
                $addedStudents++;
            }

            // Recargar la lista de estudiantes
            $this->loadGroupStudents();
            $this->reset('studentNames');
            
            session()->flash('message', $addedStudents . ' estudiantes añadidos.');
        }
    }

    private function addStudentToGroup($studentName)
    {
        DB::transaction(function () use ($studentName) {
            $student = Estudiante::firstOrCreate(['nombre' => $studentName]);

            $group = Grupo::where('id', $this->selectedGroup)
                          ->where('id_profesor', Auth::id())
                          ->firstOrFail();

            if (! $group->estudiantes()->where('id_estudiante', $student->id)->exists()) {
                $group->estudiantes()->attach($student->id);
            }
        });
    }

    // Método para eliminar un estudiante del grupo
    public function removeStudentFromGroup($studentId)
    {
        if ($this->selectedGroup) {
            $group = Grupo::where('id', $this->selectedGroup)
                         ->where('id_profesor', Auth::id())
                         ->firstOrFail();
                         
            $group->estudiantes()->detach($studentId);
            
            // Recargar la lista de estudiantes
            $this->loadGroupStudents();
            
            session()->flash('message', 'Estudiante eliminado del grupo.');
        }
    }

    // Método modificado para mostrar el modal de confirmación
    public function deleteGroup($groupId)
    {
        // Debug - agregar mensaje de log
        logger('deleteGroup llamado con ID: ' . $groupId);
        
        // Verificar que el grupo exista y pertenezca al profesor antes de mostrar el modal
        $group = Grupo::where('id', $groupId)
                      ->where('id_profesor', Auth::id())
                      ->first();

        if (!$group) {
            session()->flash('error', 'Grupo no encontrado o sin permiso.');
            return;
        }

        // Guardar el ID del grupo a eliminar y mostrar el modal de confirmación
        $this->groupToDelete = $groupId;
        logger('Mostrando modal de confirmación para grupo: ' . $groupId);
        $this->dispatch('openDeleteModal')->toBrowser();    }

    // Nuevo método para confirmar la eliminación del grupo
    public function confirmDeleteGroup()
    {
        logger('confirmDeleteGroup llamado para grupo: ' . $this->groupToDelete);
        
        if (!$this->groupToDelete) {
            logger('No hay grupo para eliminar');
            return;
        }

        $group = Grupo::where('id', $this->groupToDelete)
                      ->where('id_profesor', Auth::id())
                      ->first();

        if (!$group) {
            logger('Grupo no encontrado o sin permiso');
            session()->flash('error', 'Grupo no encontrado o sin permiso.');
            $this->dispatch('closeDeleteModal')->toBrowser();
            return;
        }

        DB::transaction(function () use ($group) {
            // Desasocia alumnos, no borres registros globales
            $group->estudiantes()->detach();
            $group->delete();
            logger('Grupo eliminado: ' . $group->id);
        });

        session()->flash('message', 'Grupo eliminado correctamente.');
        $this->loadGroups();
        $this->groupToDelete = null;
        $this->dispatch('closeDeleteModal')->toBrowser();
    }

    public function render()
    {
        return view('livewire.group-manager');
    }
}