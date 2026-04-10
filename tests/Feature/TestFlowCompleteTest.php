<?php

use App\Models\AsignacionTest;
use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Pregunta;
use App\Models\Test;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

function crearAsignacionConAlumnos(int $numAlumnos = 4, string $estado = 'pendiente'): array
{
    static $seq = 1;

    $profesor = User::factory()->create();
    $grupo = Grupo::create(['nombre_grupo' => 'Grupo Test', 'id_profesor' => $profesor->id]);
    $test = Test::create(['nombre_test' => 'Sociometria', 'descripcion' => 'Test completo']);

    $pregPref = Pregunta::create([
        'test_id' => $test->id,
        'tipo_pregunta' => 'preferencia',
        'texto_pregunta' => 'A quien eliges para trabajar?',
    ]);
    $pregRec = Pregunta::create([
        'test_id' => $test->id,
        'tipo_pregunta' => 'rechazo',
        'texto_pregunta' => 'A quien no eliges?',
    ]);

    $asignacion = AsignacionTest::create([
        'test_id' => $test->id,
        'grupo_id' => $grupo->id,
        'profesor_id' => $profesor->id,
        'clave_acceso' => 'CLAVE' . str_pad((string) $seq++, 4, '0', STR_PAD_LEFT),
        'estado' => $estado,
    ]);

    $alumnos = collect();
    for ($i = 1; $i <= $numAlumnos; $i++) {
        $alumno = Estudiante::create(['nombre' => "Alumno $i"]);
        $grupo->estudiantes()->attach($alumno->id);
        $alumnos->push($alumno);
    }

    return compact('profesor', 'grupo', 'test', 'pregPref', 'pregRec', 'asignacion', 'alumnos');
}

it('permite acceder al formulario de ingreso de clave', function () {
    $this->get(route('test.ingresar'))->assertOk();
});

it('rechaza clave inexistente', function () {
    $this->post(route('test.verificar'), ['clave_acceso' => 'NOEXISTE'])
        ->assertSessionHasErrors('clave_acceso');
});

it('rechaza clave de asignacion no pendiente', function () {
    ['asignacion' => $asignacion] = crearAsignacionConAlumnos(estado: 'aplicado');

    $this->post(route('test.verificar'), ['clave_acceso' => $asignacion->clave_acceso])
        ->assertSessionHasErrors('clave_acceso');
});

it('redirige al test correcto al ingresar clave valida', function () {
    ['asignacion' => $asignacion, 'test' => $test] = crearAsignacionConAlumnos();

    $this->post(route('test.verificar'), ['clave_acceso' => $asignacion->clave_acceso])
        ->assertRedirect(route('test.realizar', $asignacion));

    $this->get(route('test.realizar', $asignacion))
        ->assertOk()
        ->assertSee($test->nombre_test);
});

it('acepta claves de asignaciones en progreso para que el resto del grupo pueda responder', function () {
    ['asignacion' => $asignacion, 'pregPref' => $pregPref, 'pregRec' => $pregRec, 'alumnos' => $alumnos] = crearAsignacionConAlumnos();

    $respondiente = $alumnos[0];
    $otros = $alumnos->filter(fn ($a) => $a->id !== $respondiente->id)->values();

    $this->withSession(['test_access.assignment_id' => $asignacion->id])
        ->post(route('test.submit', $asignacion), [
            'estudiante_id' => $respondiente->id,
            'respuesta_' . $pregPref->id . '_1' => $otros[0]->id,
            'respuesta_' . $pregPref->id . '_2' => $otros[1]->id,
            'respuesta_' . $pregPref->id . '_3' => $otros[2]->id,
            'respuesta_' . $pregRec->id . '_1' => $otros[2]->id,
            'respuesta_' . $pregRec->id . '_2' => $otros[1]->id,
            'respuesta_' . $pregRec->id . '_3' => $otros[0]->id,
        ])
        ->assertRedirect(route('test.success'));

    expect($asignacion->fresh()->estado)->toBe('en progreso');

    $this->post(route('test.verificar'), ['clave_acceso' => $asignacion->clave_acceso])
        ->assertRedirect(route('test.realizar', $asignacion));
});

it('muestra los alumnos del grupo en el formulario del test', function () {
    ['asignacion' => $asignacion, 'alumnos' => $alumnos] = crearAsignacionConAlumnos();

    $this->withSession(['test_access.assignment_id' => $asignacion->id])
        ->get(route('test.realizar', $asignacion))
        ->assertOk()
        ->assertSee($alumnos->first()->nombre);
});

it('guarda respuestas y relaciones al enviar el test correctamente', function () {
    ['asignacion' => $asignacion, 'pregPref' => $pregPref, 'pregRec' => $pregRec, 'alumnos' => $alumnos] = crearAsignacionConAlumnos();

    $respondiente = $alumnos[0];
    $otros = $alumnos->filter(fn ($a) => $a->id !== $respondiente->id)->values();

    $this->withSession(['test_access.assignment_id' => $asignacion->id])
        ->post(route('test.submit', $asignacion), [
            'estudiante_id' => $respondiente->id,
            'respuesta_' . $pregPref->id . '_1' => $otros[0]->id,
            'respuesta_' . $pregPref->id . '_2' => $otros[1]->id,
            'respuesta_' . $pregPref->id . '_3' => $otros[2]->id,
            'respuesta_' . $pregRec->id . '_1' => $otros[2]->id,
            'respuesta_' . $pregRec->id . '_2' => $otros[1]->id,
            'respuesta_' . $pregRec->id . '_3' => $otros[0]->id,
        ])
        ->assertRedirect(route('test.success'));

    $this->assertDatabaseHas('respuestas', [
        'asignacion_test_id' => $asignacion->id,
        'alumno_id' => $respondiente->id,
    ]);

    $this->assertDatabaseHas('relaciones', [
        'asignacion_test_id' => $asignacion->id,
        'alumno_a_id' => $respondiente->id,
        'tipo_relacion' => 'preferido',
    ]);

    $this->assertDatabaseHas('relaciones', [
        'asignacion_test_id' => $asignacion->id,
        'alumno_a_id' => $respondiente->id,
        'tipo_relacion' => 'rechazado',
    ]);
});

it('impide que el mismo alumno responda dos veces', function () {
    ['asignacion' => $asignacion, 'pregPref' => $pregPref, 'pregRec' => $pregRec, 'alumnos' => $alumnos] = crearAsignacionConAlumnos();

    $respondiente = $alumnos[0];
    $otros = $alumnos->filter(fn ($a) => $a->id !== $respondiente->id)->values();

    $payload = [
        'estudiante_id' => $respondiente->id,
        'respuesta_' . $pregPref->id . '_1' => $otros[0]->id,
        'respuesta_' . $pregPref->id . '_2' => $otros[1]->id,
        'respuesta_' . $pregPref->id . '_3' => $otros[2]->id,
        'respuesta_' . $pregRec->id . '_1' => $otros[2]->id,
        'respuesta_' . $pregRec->id . '_2' => $otros[1]->id,
        'respuesta_' . $pregRec->id . '_3' => $otros[0]->id,
    ];

    $this->withSession(['test_access.assignment_id' => $asignacion->id])
        ->post(route('test.submit', $asignacion), $payload);

    // Segunda vez — el controller detecta el duplicado y falla con error de validacion
    $this->withSession(['test_access.assignment_id' => $asignacion->id])
        ->post(route('test.submit', $asignacion), $payload)
        ->assertSessionHasErrors('estudiante_id');
});

it('impide seleccionarse a si mismo como respuesta', function () {
    ['asignacion' => $asignacion, 'pregPref' => $pregPref, 'pregRec' => $pregRec, 'alumnos' => $alumnos] = crearAsignacionConAlumnos();

    $respondiente = $alumnos[0];
    $otros = $alumnos->filter(fn ($a) => $a->id !== $respondiente->id)->values();

    $this->withSession(['test_access.assignment_id' => $asignacion->id])
        ->post(route('test.submit', $asignacion), [
            'estudiante_id' => $respondiente->id,
            'respuesta_' . $pregPref->id . '_1' => $respondiente->id, // self-reference
            'respuesta_' . $pregPref->id . '_2' => $otros[1]->id,
            'respuesta_' . $pregPref->id . '_3' => $otros[2]->id,
            'respuesta_' . $pregRec->id . '_1' => $otros[2]->id,
            'respuesta_' . $pregRec->id . '_2' => $otros[1]->id,
            'respuesta_' . $pregRec->id . '_3' => $otros[0]->id,
        ])
        ->assertSessionHasErrors();

    $this->assertDatabaseCount('respuestas', 0);
});

it('actualiza el estado de la asignacion al completar todas las respuestas', function () {
    ['asignacion' => $asignacion, 'pregPref' => $pregPref, 'pregRec' => $pregRec, 'alumnos' => $alumnos] = crearAsignacionConAlumnos(numAlumnos: 4);

    foreach ($alumnos as $index => $respondiente) {
        $otros = $alumnos->filter(fn ($a) => $a->id !== $respondiente->id)->values();

        $this->withSession(['test_access.assignment_id' => $asignacion->id])
            ->post(route('test.submit', $asignacion), [
                'estudiante_id' => $respondiente->id,
                'respuesta_' . $pregPref->id . '_1' => $otros[0]->id,
                'respuesta_' . $pregPref->id . '_2' => $otros[1]->id,
                'respuesta_' . $pregPref->id . '_3' => $otros[2]->id,
                'respuesta_' . $pregRec->id . '_1' => $otros[2]->id,
                'respuesta_' . $pregRec->id . '_2' => $otros[1]->id,
                'respuesta_' . $pregRec->id . '_3' => $otros[0]->id,
            ]);
    }

    expect($asignacion->fresh()->estado)->toBe('aplicado');
});
