<?php

use App\Livewire\AnalisisSelector;
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

function crearAsignacionConAnalisisDisponible(): array
{
    static $seq = 1;

    $profesor = User::factory()->create();
    $grupo = Grupo::create([
        'nombre_grupo' => 'Grupo Analisis',
        'id_profesor' => $profesor->id,
    ]);
    $test = Test::create([
        'nombre_test' => 'Test Analisis',
        'descripcion' => 'Descripcion analisis',
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
        'clave_acceso' => 'ANALISIS' . str_pad((string) $seq++, 4, '0', STR_PAD_LEFT),
        'estado' => 'en progreso',
    ]);

    $alumnos = collect();
    for ($i = 1; $i <= 4; $i++) {
        $alumno = Estudiante::create(['nombre' => "Alumno Analisis $i"]);
        $grupo->estudiantes()->attach($alumno->id);
        $alumnos->push($alumno);
    }

    Respuesta::create([
        'alumno_id' => $alumnos[0]->id,
        'asignacion_test_id' => $asignacion->id,
        'pregunta_id' => $pregunta->id,
        'respuesta' => (string) $alumnos[1]->id,
        'orden_preferencia' => 1,
        'tipo_relacion' => 'preferencia',
    ]);

    Respuesta::create([
        'alumno_id' => $alumnos[0]->id,
        'asignacion_test_id' => $asignacion->id,
        'pregunta_id' => $pregunta->id,
        'respuesta' => (string) $alumnos[2]->id,
        'orden_preferencia' => 2,
        'tipo_relacion' => 'preferencia',
    ]);

    Respuesta::create([
        'alumno_id' => $alumnos[0]->id,
        'asignacion_test_id' => $asignacion->id,
        'pregunta_id' => $pregunta->id,
        'respuesta' => (string) $alumnos[3]->id,
        'orden_preferencia' => 3,
        'tipo_relacion' => 'preferencia',
    ]);

    return compact('profesor', 'grupo', 'test', 'pregunta', 'asignacion', 'alumnos');
}

it('preselecciona grupo y asignacion al abrir el analisis desde una asignacion concreta', function () {
    ['profesor' => $profesor, 'grupo' => $grupo, 'asignacion' => $asignacion] = crearAsignacionConAnalisisDisponible();

    $this->actingAs($profesor);

    $component = Livewire::test(AnalisisSelector::class, [
        'grupoInicial' => $grupo->id,
        'asignacionInicial' => $asignacion->id,
    ])
        ->assertSet('grupoSeleccionado', (string) $grupo->id)
        ->assertSet('asignacionTestId', $asignacion->id)
        ->assertSee('Analisis generado correctamente.');

    expect($component->instance()->analisis)->not->toBeEmpty();
    expect($component->instance()->analisis['totales']['respondieron'] ?? 0)->toBe(1);
});
