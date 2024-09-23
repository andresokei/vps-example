<?php

namespace App\Livewire;

use Livewire\Component;

class TestDebounce extends Component
{
    public $studentName = '';

    public function render()
    {
        return view('livewire.test-debounce');
    }

    public function testAction()
{
    dd('Acción disparada correctamente');
}

}
