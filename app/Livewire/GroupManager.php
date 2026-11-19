<?php

namespace App\Livewire;

use App\Livewire\Concerns\ManagesGroupStudents;
use App\Models\Grupo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class GroupManager extends Component
{
    use ManagesGroupStudents;

    public $groupName;
    public $groups;
    public $selectedGroup = null;
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

    protected function rosterGroupId(): ?int
    {
        return $this->selectedGroup;
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
