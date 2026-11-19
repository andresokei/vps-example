<?php

use App\Livewire\AnalisisSelector;
use App\Models\AsignacionTest;
use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Pregunta;
use App\Models\Relacion;
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

it('permite copiar por separado el enlace, la clave y el acceso completo', function () {
    ['profesor' => $profesor, 'asignacion' => $asignacion] = crearAsignacionConAnalisisDisponible();
    $this->actingAs($profesor);
    $html = Livewire::test(\App\Livewire\AssignedTests::class)
        ->call('seleccionarAsignacion', $asignacion->id)->html();
    $dom = new DOMDocument();
    $dom->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    $buttons = (new DOMXPath($dom))->query('//button[@data-copy-text]');
    $values = [];
    foreach ($buttons as $button) {
        $values[] = $button->getAttribute('data-copy-text');
    }
    $url = route('test.ingresar');
    expect($values)->toContain($url, $asignacion->clave_acceso, "Enlace: $url\nClave: {$asignacion->clave_acceso}");
});

it('preselecciona grupo y asignacion al abrir el analisis desde una asignacion concreta', function () {
    ['profesor' => $profesor, 'grupo' => $grupo, 'asignacion' => $asignacion] = crearAsignacionConAnalisisDisponible();

    $this->actingAs($profesor);

    $component = Livewire::test(AnalisisSelector::class, [
        'grupoInicial' => $grupo->id,
        'asignacionInicial' => $asignacion->id,
    ])
        ->assertSet('grupoSeleccionado', (string) $grupo->id)
        ->assertSet('asignacionTestId', $asignacion->id)
        ->assertSee(route('analisis.pdf', $asignacion))
        ->assertDontSee(__('Analysis generated successfully.'));

    expect($component->instance()->analisis)->not->toBeEmpty();
    expect($component->instance()->analisis['totales']['respondieron'] ?? 0)->toBe(1);
});

it('abre un unico test y mantiene el total de selecciones en el resumen', function () {
    ['profesor' => $profesor, 'grupo' => $grupo, 'asignacion' => $asignacion] = crearAsignacionConAnalisisDisponible();
    $this->actingAs($profesor);
    $contador = __('analysis_screen.choices').':</span> <strong>3</strong>';
    Livewire::test(AnalisisSelector::class)
        ->set('grupoSeleccionado', (string) $grupo->id)
        ->assertSet('asignacionTestId', $asignacion->id)
        ->assertSeeHtml($contador)
        ->call('$refresh')->assertSeeHtml($contador)
        ->assertDontSee(__('analysis_screen.change'))
        ->set('grupoSeleccionado', '')
        ->assertSet('analisis', [])->assertDontSeeHtml('id="analisisTabs"');
    expect($asignacion->respuestas()->count())->toBe(3);
});

it('distingue dos pases del mismo test y enlaza solo los resultados de la asignacion elegida', function () {
    ['profesor' => $profesor, 'grupo' => $grupo, 'asignacion' => $primera] = crearAsignacionConAnalisisDisponible();
    $profesor->grantProfesorAccess();
    $segunda = $primera->replicate();
    $segunda->clave_acceso = 'NOVIEMBRE';
    $segunda->estado = 'pendiente';
    $segunda->save();
    $this->actingAs($profesor);
    $lista = route('analisis', ['grupo' => $grupo->id]);
    $enlace = fn ($a) => route('analisis', ['grupo' => $grupo->id, 'asignacion' => $a->id]);

    $this->get($lista)->assertOk()->assertSeeText(__('analysis_screen.choose'))
        ->assertSeeInOrder([$enlace($segunda), $enlace($primera)])
        ->assertSeeText(__('analysis_screen.respondents', ['people' => 1, 'total' => 4]))
        ->assertDontSeeHtml('id="analisisTabs"')->assertDontSeeText(__('analysis_screen.choices'));
    $this->get($enlace($segunda))->assertOk()->assertSeeText(__('analysis_screen.no_answers'))
        ->assertSee($lista)->assertDontSeeHtml('id="soc-data"')->assertDontSee(route('analisis.pdf', $primera));
    $this->get($enlace($primera))->assertOk()->assertSeeText(__('analysis_screen.change'))
        ->assertSee(route('analisis.pdf', $primera))->assertSeeHtml('id="soc-data"')
        ->assertSeeHtml('id="reciprocity-data"')->assertDontSee(route('analisis.pdf', $segunda));
    Livewire::test(AnalisisSelector::class)->set('grupoSeleccionado', (string) $grupo->id)
        ->assertSet('asignacionTestId', null)->assertSet('analisis', [])->assertSee(__('analysis_screen.choose'));

    $respuesta = $primera->respuestas()->firstOrFail()->replicate();
    $respuesta->asignacion_test_id = $segunda->id;
    $respuesta->save();
    $resumen = fn ($n) => __('analysis_screen.choices').':</span> <strong>'.$n.'</strong>';
    $this->get($lista)->assertSeeText(__('analysis_screen.choose'))->assertDontSeeHtml('id="soc-data"');
    $this->get($enlace($segunda))->assertSeeHtml($resumen(1))->assertSee(route('analisis.pdf', $segunda));
    $this->get($enlace($primera))->assertSeeHtml($resumen(3))->assertSee(route('analisis.pdf', $primera));
});

it('diferencia un grupo sin asignaciones de una unica asignacion sin respuestas', function () {
    ['profesor' => $profesor, 'grupo' => $grupo, 'asignacion' => $asignacion] = crearAsignacionConAnalisisDisponible();
    $asignacion->respuestas()->delete();
    $vacio = $profesor->grupos()->create(['nombre_grupo' => 'Sin asignaciones']);
    $this->actingAs($profesor);
    Livewire::test(AnalisisSelector::class, ['grupoInicial' => $grupo->id])
        ->assertSet('asignacionTestId', $asignacion->id)->assertSee(__('analysis_screen.no_answers'))
        ->assertDontSeeHtml('id="analisisTabs"')->assertDontSee(route('analisis.pdf', $asignacion))
        ->set('grupoSeleccionado', (string) $vacio->id)->assertSet('asignacionTestId', null)
        ->assertSee(__('No test assignments found for this group.'))->assertDontSee(__('analysis_screen.no_answers'));
});

it('no regenera relaciones al previsualizar analisis en modo admin solo lectura', function () {
    ['profesor' => $profesor, 'grupo' => $grupo, 'asignacion' => $asignacion] = crearAsignacionConAnalisisDisponible();

    $this->actingAs($profesor);
    session(['impersonator_id' => 999]);

    Livewire::test(AnalisisSelector::class, [
        'grupoInicial' => $grupo->id,
        'asignacionInicial' => $asignacion->id,
    ])
        ->assertSet('grupoSeleccionado', (string) $grupo->id)
        ->assertSet('asignacionTestId', $asignacion->id)
        ->assertSee(__('This analysis needs to be regenerated before it can be previewed in read-only admin mode.'));

    expect(Relacion::where('asignacion_test_id', $asignacion->id)->count())->toBe(0);
});

it('conserva las preguntas repetidas y cuenta una vez cada par dirigido en la densidad', function () {
    ['grupo' => $grupo, 'test' => $test, 'asignacion' => $asignacion, 'alumnos' => $alumnos] = crearAsignacionConAnalisisDisponible();
    $original = $asignacion->respuestas()->where('orden_preferencia', 1)->firstOrFail();
    foreach (['preferencia', 'rechazo'] as $tipo) {
        $pregunta = $test->preguntas()->create(['tipo_pregunta' => $tipo, 'texto_pregunta' => 'Otra pregunta']);
        $respuesta = $original->replicate();
        $respuesta->pregunta_id = $pregunta->id;
        $respuesta->tipo_relacion = $tipo;
        $respuesta->save();
    }
    $reciproca = $original->replicate();
    $reciproca->alumno_id = $alumnos[1]->id;
    $reciproca->respuesta = (string) $alumnos[0]->id;
    $reciproca->save();

    Relacion::generarDesdeRespuestas($asignacion->id);
    $analisis = app(\App\Services\AnalisisGrupalService::class)->generar($grupo->id, $asignacion->id);
    $enlace = collect($analisis['sociograma']['links'])->where('source', $alumnos[0]->id)
        ->where('target', $alumnos[1]->id)->firstWhere('tipo', 'preferido');
    expect($enlace['preguntas_ids'])->toHaveCount(2)
        ->and($enlace['isMatch'])->toBeTrue()
        ->and(abs($analisis['density'] - 4 / 12))->toBeLessThan(0.0001);
});
