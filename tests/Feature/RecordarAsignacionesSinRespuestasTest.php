<?php

use App\Mail\RecordatorioAsignacionesSinRespuestasMail;
use App\Models\AsignacionTest;
use App\Models\Grupo;
use App\Models\Test;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    $this->travelTo(now('Europe/Madrid')->setTime(11, 0));
});

function asignacion(User $docente, int $diasAtras, string $estado = 'pendiente'): AsignacionTest
{
    $grupo = Grupo::create(['nombre_grupo' => '1B', 'id_profesor' => $docente->id]);
    $test = Test::create(['nombre_test' => 'Test demo', 'id_profesor' => $docente->id]);

    $a = AsignacionTest::create([
        'test_id' => $test->id, 'grupo_id' => $grupo->id, 'profesor_id' => $docente->id,
        'clave_acceso' => strtoupper(substr(md5((string) microtime(true).$docente->id.$diasAtras), 0, 8)), 'estado' => $estado,
    ]);
    $a->forceFill(['created_at' => now()->subDays($diasAtras)])->save();

    return $a;
}

function docente(array $extra = []): User
{
    return User::factory()->create(array_merge(['rol' => 'profesor', 'email_verified_at' => now()], $extra));
}

it('simula por defecto', function () {
    asignacion(docente(), 5);

    $this->artisan('asignaciones:recordar-sin-respuestas')->assertSuccessful();

    Mail::assertNothingSent();
});

it('avisa una sola vez por docente aunque tenga varias y marca las asignaciones', function () {
    $d = docente();
    $a1 = asignacion($d, 5);
    $a2 = asignacion($d, 10);

    $this->artisan('asignaciones:recordar-sin-respuestas', ['--send' => true])->assertSuccessful();
    Mail::assertSent(RecordatorioAsignacionesSinRespuestasMail::class, 1);
    expect($a1->fresh()->recordatorio_enviado_at)->not->toBeNull()
        ->and($a2->fresh()->recordatorio_enviado_at)->not->toBeNull();

    $this->artisan('asignaciones:recordar-sin-respuestas', ['--send' => true])->assertSuccessful();
    Mail::assertSent(RecordatorioAsignacionesSinRespuestasMail::class, 1);
});

it('ignora asignaciones recientes, antiguas, cerradas, con respuestas y docentes sin verificar', function () {
    $d = docente();
    asignacion($d, 1);
    asignacion($d, 60);
    asignacion($d, 6, 'aplicado');
    asignacion(docente(['email_verified_at' => null]), 6);

    $conRespuestas = asignacion($d, 6);
    $alumno = \App\Models\Estudiante::create(['nombre' => 'Alumno de prueba']);
    $pregunta = \App\Models\Pregunta::create(['test_id' => $conRespuestas->test_id, 'tipo_pregunta' => 'eleccion', 'texto_pregunta' => 'Pregunta de prueba', 'orden' => 1]);
    DB::table('respuestas')->insert([
        'alumno_id' => $alumno->id, 'asignacion_test_id' => $conRespuestas->id, 'pregunta_id' => $pregunta->id,
        'respuesta' => $alumno->id, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->artisan('asignaciones:recordar-sin-respuestas', ['--send' => true])->assertSuccessful();

    Mail::assertNothingSent();
});

it('no incluye la clave de acceso en el correo y enlaza al panel', function () {
    $d = docente(['name' => 'Ana Pérez']);
    $a = asignacion($d, 5);

    $html = (new RecordatorioAsignacionesSinRespuestasMail($d, collect([$a->load(['test', 'grupo'])])))->render();

    expect($html)->not->toContain($a->clave_acceso)
        ->toContain('Hola Ana')
        ->toContain(route('dashboard'))
        ->toContain(route('test.ingresar'));
});

it('se niega a enviar fuera de horario', function () {
    asignacion(docente(), 5);
    $this->travelTo(now('Europe/Madrid')->setTime(23, 0));

    $this->artisan('asignaciones:recordar-sin-respuestas', ['--send' => true])->assertFailed();
    Mail::assertNothingSent();
});
