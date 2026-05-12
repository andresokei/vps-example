<?php

use App\Models\AsignacionTest;
use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Pregunta;
use App\Models\Test;
use App\Models\User;
use App\Services\AnalisisGrupalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

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
        ->assertSee($alumnos->first()->nombre)
        ->assertDontSee('Preference (badge)')
        ->assertDontSee('Rejection (badge)')
        ->assertDontSee('test-form__question-badge', false);
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

it('permite enviar preguntas opcionales en blanco y registra la participacion', function () {
    ['asignacion' => $asignacion, 'pregPref' => $pregPref, 'pregRec' => $pregRec, 'alumnos' => $alumnos] = crearAsignacionConAlumnos();

    $pregPref->update(['permite_respuesta_vacia' => true]);
    $pregRec->update(['permite_respuesta_vacia' => true]);

    $respondiente = $alumnos[0];

    $this->withSession(['test_access.assignment_id' => $asignacion->id])
        ->get(route('test.realizar', $asignacion))
        ->assertOk()
        ->assertDontSeeText('Optional')
        ->assertDontSee('test-form__question-badge--optional', false);

    $this->withSession(['test_access.assignment_id' => $asignacion->id])
        ->post(route('test.submit', $asignacion), [
            'estudiante_id' => $respondiente->id,
        ])
        ->assertRedirect(route('test.success'));

    $this->assertDatabaseCount('respuestas', 2);
    $this->assertDatabaseHas('respuestas', [
        'asignacion_test_id' => $asignacion->id,
        'alumno_id' => $respondiente->id,
        'pregunta_id' => $pregPref->id,
        'respuesta' => null,
    ]);
    $this->assertDatabaseHas('respuestas', [
        'asignacion_test_id' => $asignacion->id,
        'alumno_id' => $respondiente->id,
        'pregunta_id' => $pregRec->id,
        'respuesta' => null,
    ]);
    $this->assertDatabaseCount('relaciones', 0);

    expect($asignacion->fresh()->estado)->toBe('en progreso');

    $analisis = app(AnalisisGrupalService::class)->generar($asignacion->grupo_id, $asignacion->id, true);

    expect($analisis['totales']['respondieron'])->toBe(1)
        ->and($analisis['totales']['relaciones'])->toBe(0);

    $this->withSession(['test_access.assignment_id' => $asignacion->id])
        ->post(route('test.submit', $asignacion), [
            'estudiante_id' => $respondiente->id,
        ])
        ->assertSessionHasErrors('estudiante_id');
});

it('permite responder parcialmente una pregunta opcional', function () {
    ['asignacion' => $asignacion, 'pregPref' => $pregPref, 'pregRec' => $pregRec, 'alumnos' => $alumnos] = crearAsignacionConAlumnos();

    $pregRec->update(['permite_respuesta_vacia' => true]);

    $respondiente = $alumnos[0];
    $otros = $alumnos->filter(fn ($a) => $a->id !== $respondiente->id)->values();

    $this->withSession(['test_access.assignment_id' => $asignacion->id])
        ->post(route('test.submit', $asignacion), [
            'estudiante_id' => $respondiente->id,
            'respuesta_' . $pregPref->id . '_1' => $otros[0]->id,
            'respuesta_' . $pregPref->id . '_2' => $otros[1]->id,
            'respuesta_' . $pregPref->id . '_3' => $otros[2]->id,
            'respuesta_' . $pregRec->id . '_1' => $otros[2]->id,
        ])
        ->assertRedirect(route('test.success'));

    $this->assertDatabaseCount('respuestas', 4);
    $this->assertDatabaseCount('relaciones', 4);
});

it('ajusta el numero de selecciones requeridas cuando el grupo tiene menos de cuatro alumnos', function () {
    ['asignacion' => $asignacion, 'pregPref' => $pregPref, 'pregRec' => $pregRec, 'alumnos' => $alumnos] = crearAsignacionConAlumnos(numAlumnos: 3);

    $respondiente = $alumnos[0];
    $otros = $alumnos->filter(fn ($a) => $a->id !== $respondiente->id)->values();

    $this->withSession(['test_access.assignment_id' => $asignacion->id])
        ->get(route('test.realizar', $asignacion))
        ->assertOk()
        ->assertSeeText('Choose different classmates for each question.');

    $this->withSession(['test_access.assignment_id' => $asignacion->id])
        ->post(route('test.submit', $asignacion), [
            'estudiante_id' => $respondiente->id,
            'respuesta_' . $pregPref->id . '_1' => $otros[0]->id,
            'respuesta_' . $pregPref->id . '_2' => $otros[1]->id,
            'respuesta_' . $pregRec->id . '_1' => $otros[1]->id,
            'respuesta_' . $pregRec->id . '_2' => $otros[0]->id,
        ])
        ->assertRedirect(route('test.success'));

    $this->assertDatabaseCount('respuestas', 4);
    $this->assertDatabaseCount('relaciones', 4);
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

it('reconstruye relaciones normalizadas e invalida cache al registrar nuevas respuestas', function () {
    ['grupo' => $grupo, 'asignacion' => $asignacion, 'pregPref' => $pregPref, 'pregRec' => $pregRec, 'alumnos' => $alumnos] = crearAsignacionConAlumnos();

    $pregPrefExtra = Pregunta::create([
        'test_id' => $asignacion->test_id,
        'tipo_pregunta' => 'preferencia',
        'texto_pregunta' => 'Con quien quieres colaborar tambien?',
        'orden' => 3,
    ]);

    $service = app(AnalisisGrupalService::class);

    $primerAnalisis = $service->generar($grupo->id, $asignacion->id);

    expect($primerAnalisis['totales']['relaciones'])->toBe(0)
        ->and($primerAnalisis['totales']['respondieron'])->toBe(0);

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
            'respuesta_' . $pregPrefExtra->id . '_1' => $otros[0]->id,
            'respuesta_' . $pregPrefExtra->id . '_2' => $otros[2]->id,
            'respuesta_' . $pregPrefExtra->id . '_3' => $otros[1]->id,
        ])
        ->assertRedirect(route('test.success'));

    $analisisActualizado = $service->generar($grupo->id, $asignacion->id);

    expect($analisisActualizado['totales']['respondieron'])->toBe(1)
        ->and($analisisActualizado['totales']['relaciones'])->toBe(6)
        ->and($analisisActualizado['totales']['preferencias'])->toBe(3)
        ->and($analisisActualizado['totales']['rechazos'])->toBe(3);

    $this->assertDatabaseCount('respuestas', 9);
    $this->assertDatabaseCount('relaciones', 6);
});

it('protege la tabla de respuestas contra duplicados por pregunta y orden', function () {
    ['asignacion' => $asignacion, 'pregPref' => $pregPref, 'alumnos' => $alumnos] = crearAsignacionConAlumnos();

    DB::table('respuestas')->insert([
        'alumno_id' => $alumnos[0]->id,
        'asignacion_test_id' => $asignacion->id,
        'pregunta_id' => $pregPref->id,
        'respuesta' => (string) $alumnos[1]->id,
        'orden_preferencia' => 1,
        'tipo_relacion' => 'preferencia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->expectException(\Illuminate\Database\QueryException::class);

    DB::table('respuestas')->insert([
        'alumno_id' => $alumnos[0]->id,
        'asignacion_test_id' => $asignacion->id,
        'pregunta_id' => $pregPref->id,
        'respuesta' => (string) $alumnos[2]->id,
        'orden_preferencia' => 1,
        'tipo_relacion' => 'preferencia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});
