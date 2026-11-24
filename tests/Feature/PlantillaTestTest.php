<?php

use App\Models\Test;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('shares an ordered bilingual questionnaire between teachers as an independent copy', function () {
    $autor = User::factory()->create();
    $receptor = User::factory()->create();
    $autor->grantProfesorAccess();
    $receptor->grantProfesorAccess();
    $test = Test::create(['id_profesor' => $autor->id, 'nombre_test' => 'Convivencia', 'nombre_test_en' => 'Class life',
        'descripcion' => 'Inicio de curso', 'descripcion_en' => 'Start of term', 'max_respuestas' => 4]);
    $test->preguntas()->create(['orden' => 4, 'texto_pregunta' => '¿Con quién evitarías trabajar?',
        'tipo_pregunta' => 'rechazo', 'permite_respuesta_vacia' => true]);
    $test->preguntas()->create(['orden' => 2, 'texto_pregunta' => '¿Con quién trabajarías?',
        'texto_pregunta_en' => 'Who would you work with?', 'tipo_pregunta' => 'preferencia', 'permite_respuesta_vacia' => false]);

    $ruta = route('tests.plantilla.exportar', $test);
    $export = $this->actingAs($autor)->get($ruta)->assertOk()->assertDownload('convivencia.sociogram.json');
    expect(array_keys($export->json()))->toBe(['formato', 'version', 'test', 'preguntas'])
        ->and(array_keys($export->json('test')))->toBe(['nombre_test', 'nombre_test_en', 'descripcion', 'descripcion_en', 'max_respuestas'])
        ->and(array_keys($export->json('preguntas.0')))->toBe(['texto_pregunta', 'texto_pregunta_en', 'tipo_pregunta', 'permite_respuesta_vacia']);
    $this->actingAs($receptor)->get($ruta)->assertNotFound();
    $importar = function () use ($export) {
        return $this->post(route('tests.plantilla.importar'), [
            'plantilla' => UploadedFile::fake()->createWithContent('test.json', $export->getContent()),
        ])->assertSessionHasNoErrors()->assertRedirect(route('tests.index'));
    };
    $importar();
    $copia = Test::where('id_profesor', $receptor->id)->where('nombre_test', 'Convivencia')->sole();
    $this->get(route('tests.index'))->assertOk()->assertSee(route('tests.plantilla.exportar', $copia));
    expect($copia->max_respuestas)->toBe(4)
        ->and($this->get(route('tests.plantilla.exportar', $copia))->json())->toBe($export->json());
    $importar();
    expect(Test::where('id_profesor', $receptor->id)->where('nombre_test', 'Convivencia (1)')->exists())->toBeTrue();
    $copia->preguntas()->first()->update(['texto_pregunta' => 'Pregunta editada']);
    expect($test->preguntas()->first()->texto_pregunta)->toBe('¿Con quién trabajarías?');
});

it('rejects unsupported or incomplete files without importing partial tests', function () {
    $user = User::factory()->create();
    $user->grantProfesorAccess();
    $this->actingAs($user);
    $plantilla = ['formato' => 'sociogram.test', 'version' => 1, 'test' => ['nombre_test' => 'Vacío', 'max_respuestas' => 1], 'preguntas' => []];
    $pregunta = ['texto_pregunta' => '¿Con quién?', 'tipo_pregunta' => 'preferencia', 'permite_respuesta_vacia' => true];
    $cantidad = Test::count();
    foreach (['texto', json_encode(array_merge($plantilla, ['version' => 2])),
        json_encode(array_merge($plantilla, ['preguntas' => [$pregunta, []]])),
        json_encode(array_merge($plantilla, ['test' => $plantilla['test'] + ['id_profesor' => 5]]))] as $archivo) {
        $this->from(route('tests.index'))->post(route('tests.plantilla.importar'), [
            'plantilla' => UploadedFile::fake()->createWithContent('test.json', $archivo),
        ])->assertRedirect(route('tests.index'))->assertSessionHasErrors('plantilla');
        expect(Test::count())->toBe($cantidad);
    }
    $this->post(route('tests.plantilla.importar'), ['plantilla' => UploadedFile::fake()->createWithContent('vacio.json', json_encode($plantilla))])
        ->assertSessionHasNoErrors();
    expect(Test::count())->toBe($cantidad + 1);
});
