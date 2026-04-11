<?php

namespace App\Livewire;

use App\Models\AsignacionTest;
use App\Models\Grupo;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class OnboardingChecklist extends Component
{
    public bool $visible = true;
    public bool $hasGroups = false;
    public bool $hasStudents = false;
    public bool $hasTests = false;
    public int $completedCount = 0;

    public function mount(): void
    {
        if (Auth::user()->onboarding_dismissed_at) {
            $this->visible = false;
            return;
        }

        $this->loadProgress();
    }

    #[On('onboarding-progress-updated')]
    public function loadProgress(): void
    {
        $userId = Auth::id();

        $this->hasGroups   = Grupo::where('id_profesor', $userId)->exists();
        $this->hasStudents = Grupo::where('id_profesor', $userId)->whereHas('estudiantes')->exists();
        $this->hasTests    = AsignacionTest::where('profesor_id', $userId)->exists();

        $this->completedCount = (int)$this->hasGroups + (int)$this->hasStudents + (int)$this->hasTests;

        if ($this->hasGroups && $this->hasStudents && $this->hasTests) {
            Auth::user()->update(['onboarding_dismissed_at' => now()]);
            $this->dispatch('onboarding-all-done');
        }
    }

    public function dismiss(): void
    {
        Auth::user()->update(['onboarding_dismissed_at' => now()]);
        $this->visible = false;
    }

    public function render()
    {
        return view('livewire.onboarding-checklist');
    }
}
