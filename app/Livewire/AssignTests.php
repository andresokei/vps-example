<?php

namespace App\Livewire;

use App\Models\AsignacionTest;
use App\Models\Grupo;
use App\Models\Test;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AssignTests extends Component
{
    public $grupos;
    public $tests;
    public $grupo_id = '';
    public $test_id = '';

    public $successMessage;
    public $errorMessage;

    protected $listeners = ['refreshAssignedTests' => 'reloadGroups'];

    protected function rules()
    {
        return [
            'grupo_id' => [
                'required',
                'integer',
                Rule::exists('grupos', 'id')->where(fn ($query) => $query->where('id_profesor', Auth::id())),
            ],
            'test_id' => [
                'required',
                'integer',
                Rule::exists('tests', 'id'),
            ],
        ];
    }

    public function mount()
    {
        $this->grupos = Grupo::where('id_profesor', Auth::id())->orderBy('nombre_grupo')->get();
        $this->tests = Test::orderBy('nombre_test')->get();
    }

    public function reloadGroups(): void
    {
        $this->grupos = Grupo::where('id_profesor', Auth::id())->orderBy('nombre_grupo')->get();
    }

    public function assign()
    {
        try {
            $this->validate();

            $grupo = Grupo::where('id', $this->grupo_id)
                ->where('id_profesor', Auth::id())
                ->first();

            if (! $grupo) {
                $this->successMessage = null;
                $this->errorMessage = __('The selected group does not exist or you do not have permission to assign tests to it.');
                return;
            }

            $exists = AsignacionTest::where('grupo_id', $this->grupo_id)
                ->where('test_id', $this->test_id)
                ->exists();

            if ($exists) {
                $this->successMessage = null;
                $this->errorMessage = __('This test is already assigned to that group.');
                return;
            }

            DB::transaction(function () {
                AsignacionTest::create([
                    'grupo_id' => $this->grupo_id,
                    'test_id' => $this->test_id,
                    'estado' => 'pendiente',
                    'clave_acceso' => Str::upper(Str::random(8)),
                    'profesor_id' => Auth::id(),
                ]);
            });

            $this->successMessage = __('Test assigned successfully.');
            $this->errorMessage = null;
            $this->reset(['grupo_id', 'test_id']);

            $this->dispatch('refreshAssignedTests');
            $this->dispatch('onboarding-progress-updated');
        } catch (\Throwable $e) {
            $this->successMessage = null;
            $this->errorMessage = __('Error assigning test: :error', ['error' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.assign-tests');
    }

    public function resetMessages()
    {
        $this->reset(['successMessage', 'errorMessage']);
    }
}
