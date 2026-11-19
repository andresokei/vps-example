<?php

namespace App\Livewire\Concerns;

use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

trait ManagesGroupStudents
{
    use WithFileUploads;

    public $studentNames = '';
    public $csvFile;
    public $groupStudents = [];

    abstract protected function rosterGroupId(): ?int;

    private function rosterGroup()
    {
        return Grupo::where('id_profesor', Auth::id())->whereKey($this->rosterGroupId());
    }

    public function loadGroupStudents(): void
    {
        $grupo = $this->rosterGroup()->first();
        $this->groupStudents = $grupo
            ? $grupo->estudiantes()->orderBy('nombre')->orderBy('estudiantes.id')->get()
            : [];
    }

    public function uploadCSV(): void
    {
        $this->validate(['csvFile' => 'required|file|mimes:csv,txt|max:2048']);
        $file = fopen($this->csvFile->getRealPath(), 'r');
        $names = [];
        try {
            while (($row = fgetcsv($file, 0, ',', '"', '')) !== false) {
                $name = trim(preg_replace('/^\xEF\xBB\xBF/', '', $row[0] ?? ''));
                if ($name !== '') {
                    $names[] = $name;
                }
                if (count($names) > 1000) {
                    $this->addError('csvFile', __('classroom.import_limit'));
                    return;
                }
            }
        } finally {
            fclose($file);
        }
        $this->importNames($names, 'csvFile');
    }

    public function addStudentFromList(): void
    {
        $this->validate(['studentNames' => 'required|string|max:10000']);
        $parts = preg_split('/[,\r\n]+/', $this->studentNames);
        $names = array_filter(array_map('trim', $parts), fn ($name) => $name !== '');
        $this->importNames($names, 'studentNames');
    }

    private function importNames(array $names, string $field): void
    {
        if (!$names || collect($names)->contains(fn ($name) => !mb_check_encoding($name, 'UTF-8') || mb_strlen($name) > 255)) {
            $this->addError($field, __('classroom.invalid_names'));
            return;
        }
        $added = DB::transaction(function () use ($names) {
            $group = $this->rosterGroup()->lockForUpdate()->firstOrFail();
            $added = 0;
            foreach ($names as $name) {
                if ($group->estudiantes()->where('nombre', $name)->exists()) {
                    continue;
                }
                $student = Estudiante::create(['nombre' => $name]);
                $group->estudiantes()->attach($student->id);
                $added++;
            }
            return $added;
        });
        $this->reset($field);
        $this->loadGroupStudents();
        $this->dispatch('onboarding-progress-updated');
        $repeated = count($names) - $added;
        $message = __('audit.import', ['added' => $added, 'existing' => $repeated]);
        session()->flash('message', $message.($repeated ? ' '.__('audit.homonyms') : ''));
    }

    public function removeStudentFromGroup(int $studentId): void
    {
        $this->rosterGroup()->firstOrFail()->estudiantes()->detach($studentId);
        $this->loadGroupStudents();
        session()->flash('message', __('Student removed from group.'));
    }
}
