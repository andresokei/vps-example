<?php

use App\Livewire\AssignedTests;
use App\Models\AsignacionTest;
use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Test;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(Tests\TestCase::class, RefreshDatabase::class);

function crearAsignacionParaPanelProfesor(int $numAlumnos = 4): array
{
    static $seq = 1;

    $profesor = User::factory()->create();
    $grupo = Grupo::create([
        'nombre_grupo' => 'Grupo Panel',
        'id_profesor' => $profesor->id,
    ]);
    $test = Test::create([
        'nombre_test' => 'Test Panel',
        'descripcion' => 'Descripcion panel',
    ]);
    $pregunta = Pregunta::create([
        'test_id' => $test->id,
        'tipo_pregunta' => 'preferencia',
        'texto_pregunta' => 'A quien eliges?',
    ]);

    $asignacion = AsignacionTest::create([
        'test_id' => $test->id,
        'grupo_id' => $grupo->id,
        'profesor_id' => $profesor->id,
        'clave_acceso' => 'PANEL' . str_pad((string) $seq++, 4, '0', STR_PAD_LEFT),
        'estado' => 'pendiente',
    ]);

    $alumnos = collect();
    for ($i = 1; $i <= $numAlumnos; $i++) {
        $alumno = Estudiante::create(['nombre' => "Alumno Panel $i"]);
        $grupo->estudiantes()->attach($alumno->id);
        $alumnos->push($alumno);
    }

    return compact('profesor', 'grupo', 'test', 'pregunta', 'asignacion', 'alumnos');
}

it('muestra alumnos pendientes y progreso al seleccionar una asignacion', function () {
    ['profesor' => $profesor, 'pregunta' => $pregunta, 'asignacion' => $asignacion, 'alumnos' => $alumnos] = crearAsignacionParaPanelProfesor();

    Respuesta::create([
        'alumno_id' => $alumnos[0]->id,
        'asignacion_test_id' => $asignacion->id,
        'pregunta_id' => $pregunta->id,
        'respuesta' => (string) $alumnos[1]->id,
        'orden_preferencia' => 1,
        'tipo_relacion' => 'preferencia',
    ]);

    $this->actingAs($profesor);

    $component = Livewire::test(AssignedTests::class)
        ->call('seleccionarAsignacion', $asignacion->id)
        ->assertSee(route('analisis', ['grupo' => $asignacion->grupo_id, 'asignacion' => $asignacion->id]))
        ->assertSee('Alumno Panel 2')
        ->assertSee('Alumno Panel 3')
        ->assertSee('Alumno Panel 4');

    expect($component->instance()->asignacionSeleccionada->progreso_respondieron)->toBe(1);
    expect($component->instance()->asignacionSeleccionada->alumnos_pendientes_count)->toBe(3);
});

it('permite reabrir una asignacion incompleta', function () {
    ['profesor' => $profesor, 'pregunta' => $pregunta, 'asignacion' => $asignacion, 'alumnos' => $alumnos] = crearAsignacionParaPanelProfesor();

    Respuesta::create([
        'alumno_id' => $alumnos[0]->id,
        'asignacion_test_id' => $asignacion->id,
        'pregunta_id' => $pregunta->id,
        'respuesta' => (string) $alumnos[1]->id,
        'orden_preferencia' => 1,
        'tipo_relacion' => 'preferencia',
    ]);

    $this->actingAs($profesor);

    Livewire::test(AssignedTests::class)
        ->call('cerrarAsignacion', $asignacion->id);

    expect($asignacion->fresh()->estado)->toBe('aplicado');

    Livewire::test(AssignedTests::class)
        ->call('reabrirAsignacion', $asignacion->id);

    expect($asignacion->fresh()->estado)->toBe('en progreso');
});
