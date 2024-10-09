<?php

namespace App\Livewire;

use App\Models\Estudiante;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Grupo;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class GroupManager extends Component
{
    use WithFileUploads;
    

    public $groupName;
    public $groups;
    public $selectedGroup = null;
    public $studentNames = ''; // Para el campo de nombres separados por comas
    public $csvFile; // Para el archivo CSV
    public $isModalOpen = false; // Controla si el modal está abierto o cerrado

      // Método para abrir el modal
      public function openModal($groupId)
      {
          $this->selectedGroup = $groupId;
          $this->dispatch('openModal');  // Emitir el evento para abrir el modal
      }
  
      // Método para cerrar el modal
      public function closeModal()
      {
          $this->dispatch('closeModal');  // Emitir el evento para cerrar el modal
      }

       // Método para subir estudiantes desde un CSV
       public function uploadCSV()
       {
           // Validate that a file has been selected
           $this->validate([
               'csvFile' => 'required|file|mimes:csv,txt|max:2048',
           ]);
       
           // Read the CSV file and extract the names
           $file = fopen($this->csvFile->getRealPath(), 'r');
           $students = [];
           while (($data = fgetcsv($file)) !== false) {
               if (!empty($data[0])) {
                   $students[] = trim($data[0]); // Assuming the student name is in the first column
               }
           }
           fclose($file);
       
           // Add students to the group
           foreach ($students as $studentName) {
               $this->addStudentToGroup($studentName);
           }
       
           // Clear the file input and show a success message
           $this->csvFile = null;
           session()->flash('message', 'Students have been added to the group from the CSV file.');
       }
  
    // Método para crear un grupo
    public function createGroup()
    {
        Grupo::create([
            'nombre_grupo' => $this->groupName,
            'id_profesor' => Auth::user()->id,  // Asociar el grupo al profesor autenticado
        ]);

        $this->groupName = ''; // Limpiar el campo
        $this->groups = Grupo::where('id_profesor', Auth::user()->id)->get();  // Actualizar los grupos del profesor autenticado

        // Emitir un evento para notificar a otros componentes
        $this->dispatch('grupoCreado');
    }

    // Método mount
    public function mount()
    {
        $this->groups = Grupo::where('id_profesor', Auth::user()->id)->get();  // Cargar solo los grupos del profesor autenticado
    }

    // Método para añadir estudiantes desde una lista separada por comas
    public function addStudentFromList()
    {
        if ($this->selectedGroup && !empty($this->studentNames)) {
            $namesArray = array_map('trim', explode(',', $this->studentNames));

            foreach ($namesArray as $name) {
                $this->addStudentToGroup($name);
            }

            $this->studentNames = '';  // Limpiar el campo
            session()->flash('message', 'Los estudiantes han sido añadidos al grupo.');
        } else {
            session()->flash('error', 'Debes seleccionar un grupo e introducir los nombres de los estudiantes.');
        }

        $this->closeModal(); // Cerrar modal después de añadir estudiantes
    }

    // Método para añadir estudiantes desde un archivo CSV
    public function addStudentFromCSV()
    {
        if ($this->selectedGroup && $this->csvFile) {
            // Validar que el archivo es un CSV
            $this->validate([
                'csvFile' => 'required|mimes:csv,txt|max:2048',
            ]);

            // Leer el archivo CSV y añadir los estudiantes
            $filePath = $this->csvFile->getRealPath();
            $file = fopen($filePath, 'r');

            while (($row = fgetcsv($file)) !== false) {
                if (!empty($row[0])) {
                    $this->addStudentToGroup(trim($row[0])); // Añadir cada estudiante
                }
            }

            fclose($file);

            $this->csvFile = null;  // Limpiar el campo
            session()->flash('message', 'Los estudiantes han sido añadidos al grupo desde el archivo CSV.');
        } else {
            session()->flash('error', 'Debes seleccionar un grupo y subir un archivo CSV válido.');
        }

        $this->closeModal(); // Cerrar modal después de añadir estudiantes
    }

    // Método reutilizable para añadir un estudiante individualmente
    private function addStudentToGroup($studentName)
    {
        DB::transaction(function () use ($studentName) {
            $student = Estudiante::firstOrCreate(['nombre' => $studentName]);
    
            $group = Grupo::findOrFail($this->selectedGroup);
            
            if (!$group->students()->where('id_estudiante', $student->id)->exists()) {
                $group->students()->attach($student->id);
            }
        });
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

    public function deleteGroup($groupId)
    {
        DB::transaction(function () use ($groupId) {
            // Encuentra el grupo por su ID y verifica que pertenece al profesor autenticado
            $group = Grupo::where('id', $groupId)
                          ->where('id_profesor', Auth::user()->id)
                          ->first();
    
            if ($group) {
                // Eliminar los estudiantes que pertenecen al grupo
                $students = $group->students; // Asumiendo que hay una relación definida llamada "students" en el modelo Grupo
                foreach ($students as $student) {
                    $student->delete(); // Eliminar cada estudiante del grupo
                }
    
                // Eliminar el grupo de la base de datos
                $group->delete();
    
                // Actualizar la lista de grupos después de eliminar uno
                $this->groups = Grupo::where('id_profesor', Auth::user()->id)->get();
    
                session()->flash('message', 'El grupo y los estudiantes relacionados han sido eliminados exitosamente.');
            } else {
                session()->flash('error', 'No se pudo encontrar el grupo o no tienes permiso para eliminarlo.');
            }
        });
    }
    

    public function render()
    {
        return view('livewire.group-manager');
    }
}
