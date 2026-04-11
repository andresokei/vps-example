<?php

namespace App\Livewire;

use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class GroupManager extends Component
{
    use WithFileUploads;

    public $groupName;
    public $groups;
    public $selectedGroup = null;
    public $studentNames = '';
    public $csvFile;
    public $groupStudents = [];
    public $groupToDelete = null;

    protected function rules()
    {
        return [
            'groupName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('grupos', 'nombre_grupo')->where('id_profesor', Auth::id()),
            ],
            'studentNames' => ['nullable', 'string', 'max:500', 'regex:/^[\pL\s,]+$/u'],
            'csvFile' => ['nullable', 'file', 'mimes:csv,txt', 'max:2048'],
        ];
    }

    public function mount()
    {
        $this->loadGroups();
    }

    private function loadGroups()
    {
        $this->groups = Grupo::where('id_profesor', Auth::id())
            ->orderBy('nombre_grupo')
            ->get();
    }

    public function openModal($groupId)
    {
        if (! Grupo::where('id', $groupId)->where('id_profesor', Auth::id())->exists()) {
            abort(403);
        }

        $this->selectedGroup = $groupId;
        $this->loadGroupStudents();
        $this->dispatch('openModal');
    }

    public function loadGroupStudents()
    {
        if (! $this->selectedGroup) {
            $this->groupStudents = [];
            return;
        }

        $group = Grupo::where('id', $this->selectedGroup)
            ->where('id_profesor', Auth::id())
            ->first();

        $this->groupStudents = $group ? $group->estudiantes : [];
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
            'id_profesor' => Auth::id(),
        ]);

        session()->flash('message', __('Group created successfully.'));
        $this->groupName = '';
        $this->loadGroups();
        $this->dispatch('refreshAssignedTests');
        $this->dispatch('onboarding-progress-updated');
    }

    public function uploadCSV()
    {
        $this->validateOnly('csvFile');

        $path = $this->csvFile->getRealPath();
        $file = fopen($path, 'r');
        $rows = 0;
        $addedStudents = 0;

        while (($data = fgetcsv($file)) !== false && $rows < 1000) {
            if (! empty($data[0])) {
                $this->addStudentToGroup(trim($data[0]));
                $addedStudents++;
            }

            $rows++;
        }

        fclose($file);
        $this->reset('csvFile');
        $this->loadGroupStudents();
        $this->dispatch('onboarding-progress-updated');

        session()->flash('message', __(':count students added from CSV.', ['count' => $addedStudents]));
    }

    public function addStudentFromList()
    {
        $this->validateOnly('studentNames');

        if (! $this->selectedGroup) {
            return;
        }

        $names = array_filter(array_map('trim', explode(',', $this->studentNames)));
        $addedStudents = 0;

        foreach ($names as $name) {
            $this->addStudentToGroup($name);
            $addedStudents++;
        }

        $this->loadGroupStudents();
        $this->reset('studentNames');
        $this->dispatch('onboarding-progress-updated');

        session()->flash('message', __(':count students added.', ['count' => $addedStudents]));
    }

    private function addStudentToGroup($studentName)
    {
        DB::transaction(function () use ($studentName) {
            $group = Grupo::where('id', $this->selectedGroup)
                ->where('id_profesor', Auth::id())
                ->firstOrFail();

            $studentName = trim($studentName);
            if ($studentName === '') {
                return;
            }

            $student = $group->estudiantes()
                ->where('nombre', $studentName)
                ->first();

            if (! $student) {
                $student = Estudiante::create(['nombre' => $studentName]);
            }

            if (! $group->estudiantes()->where('id_estudiante', $student->id)->exists()) {
                $group->estudiantes()->attach($student->id);
            }
        });
    }

    public function removeStudentFromGroup($studentId)
    {
        if (! $this->selectedGroup) {
            return;
        }

        $group = Grupo::where('id', $this->selectedGroup)
            ->where('id_profesor', Auth::id())
            ->firstOrFail();

        $group->estudiantes()->detach($studentId);
        $this->loadGroupStudents();

        session()->flash('message', __('Student removed from group.'));
    }

    public function deleteGroup($groupId)
    {
        $group = Grupo::where('id', $groupId)
            ->where('id_profesor', Auth::id())
            ->first();

        if (! $group) {
            session()->flash('error', __('Group not found or no permission.'));
            return;
        }

        $this->groupToDelete = $groupId;
        $this->dispatch('openDeleteModal');
    }

    public function confirmDeleteGroup()
    {
        if (! $this->groupToDelete) {
            return;
        }

        $group = Grupo::where('id', $this->groupToDelete)
            ->where('id_profesor', Auth::id())
            ->first();

        if (! $group) {
            session()->flash('error', __('Group not found or no permission.'));
            $this->dispatch('closeDeleteModal');
            return;
        }

        DB::transaction(function () use ($group) {
            $group->estudiantes()->detach();
            $group->delete();
        });

        session()->flash('message', __('Group deleted successfully.'));
        $this->loadGroups();
        $this->groupToDelete = null;
        $this->dispatch('closeDeleteModal');
    }

    public function render()
    {
        return view('livewire.group-manager');
    }
}
