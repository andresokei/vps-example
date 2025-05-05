<?php
// app/Livewire/AssignTests.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Grupo;
use App\Models\Test;
use App\Models\AsignacionTest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;

class AssignTests extends Component
{
    use AuthorizesRequests;

    public $grupos;
    public $tests;
    public $grupo_id = '';
    public $test_id = '';
    
    public $successMessage;
    public $errorMessage;

    protected $listeners = ['refreshAssignedTests' => '$refresh'];

    public function updated($propertyName)
    {
        \Log::info('Propiedad actualizada: ' . $propertyName, [$this->$propertyName]);
    }

    protected function rules()
    {
        return [
            'grupo_id' => [
                'required',
                'integer',
                Rule::exists('grupos','id')->where(fn($q) => $q->where('id_profesor', Auth::id())),
            ],
            'test_id' => [
                'required',
                'integer',
                Rule::exists('tests','id'),
            ],
        ];
    }

    public function mount()
    {
        // Traemos solo los grupos donde id_profesor es el usuario actual
        $this->grupos = Grupo::where('id_profesor', Auth::id())->get();
        $this->tests  = Test::all();
        
        \Log::info('Componente AssignTests montado', [
            'grupos_count' => $this->grupos->count(),
            'tests_count' => $this->tests->count(),
        ]);
    }

    public function assign()
    {
        \Log::info('Método assign llamado', [
            'grupo_id' => $this->grupo_id,
            'test_id' => $this->test_id
        ]);
        
        try {
            $validatedData = $this->validate();
            
            \Log::info('Datos validados', $validatedData);
            
            // Aseguramos que el grupo realmente pertenece al usuario
            $grupo = Grupo::where('id', $this->grupo_id)
                         ->where('id_profesor', Auth::id())
                         ->first();
                         
            if (!$grupo) {
                $this->errorMessage = 'El grupo seleccionado no existe o no tienes permiso para asignarle tests.';
                \Log::error('Grupo no encontrado o no pertenece al usuario', [
                    'grupo_id' => $this->grupo_id,
                    'user_id' => Auth::id()
                ]);
                return;
            }
            
            // Verificamos que tenemos permiso mediante la policy
            try {
                // Comenta esta línea si no tienes configurada una policy
                // $this->authorize('assignTest', $grupo);
            } catch (\Exception $e) {
                $this->errorMessage = 'No tienes permiso para asignar tests a este grupo.';
                \Log::error('Error de autorización', ['error' => $e->getMessage()]);
                return;
            }

            // Evitar duplicados
            $exists = AsignacionTest::where('grupo_id', $this->grupo_id)
                                   ->where('test_id', $this->test_id)
                                   ->exists();
                                   
            if ($exists) {
                $this->errorMessage = 'Este test ya está asignado a ese grupo.';
                \Log::warning('Intento de asignación duplicada', [
                    'grupo_id' => $this->grupo_id,
                    'test_id' => $this->test_id
                ]);
                return;
            }

            DB::beginTransaction();
            
            // Creamos la asignación
            $asignacion = AsignacionTest::create([
                'grupo_id'     => $this->grupo_id,
                'test_id'      => $this->test_id,
                'estado'       => 'pendiente',
                'clave_acceso' => Str::upper(Str::random(8)),
                'profesor_id'  => Auth::id(),
            ]);
            
            \Log::info('Asignación creada antes de commit', [
                'asignacion_id' => $asignacion->id ?? 'No ID'
            ]);
            
            DB::commit();
            
            \Log::info('Test asignado correctamente', [
                'grupo_id' => $this->grupo_id,
                'test_id' => $this->test_id,
                'asignacion_id' => $asignacion->id ?? 'No ID'
            ]);
            
            $this->successMessage = 'Test asignado correctamente.';
            $this->reset(['grupo_id', 'test_id']);
            
            // Compatibilidad con diferentes versiones de Livewire
            if (method_exists($this, 'dispatch')) {
                $this->dispatch('refreshAssignedTests'); // Livewire v3
                \Log::info('Dispatch refreshAssignedTests enviado (Livewire v3)');
            } else {
                $this->emit('refreshAssignedTests'); // Livewire v2
                \Log::info('Emit refreshAssignedTests enviado (Livewire v2)');
            }
            
            $this->dispatch('toast', ['message' => 'Test asignado correctamente']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Error al asignar el test: ' . $e->getMessage();
            \Log::error('Error al asignar test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'grupo_id' => $this->grupo_id,
                'test_id' => $this->test_id
            ]);
        }
    }

    public function render()
    {
        \Log::info('Renderizando componente AssignTests');
        
        // Recargamos la lista de tests asignados para mantenerla actualizada
        $testAsignados = AsignacionTest::with(['grupo', 'test'])
                                      ->whereHas('grupo', function($query) {
                                          $query->where('id_profesor', Auth::id());
                                      })
                                      ->latest()
                                      ->get();
                                      
        \Log::info('Tests asignados cargados', [
            'count' => $testAsignados->count()
        ]);
                                      
        return view('livewire.assign-tests', [
            'testAsignados' => $testAsignados
        ]);
    }
    
    // Método para limpiar mensajes
    public function resetMessages()
    {
        $this->reset(['successMessage', 'errorMessage']);
    }
}