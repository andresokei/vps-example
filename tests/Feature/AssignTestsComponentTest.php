<?php

use App\Livewire\AssignTests;
use App\Models\AsignacionTest;
use App\Models\Grupo;
use App\Models\Pregunta;
use App\Models\Test;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('impide asignar un test sin preguntas', function () {
    $profesor = User::factory()->create();
    $grupo = Grupo::create([
        'nombre_grupo' => 'Grupo sin preguntas',
        'id_profesor' => $profesor->id,
    ]);
    $test = Test::create([
        'nombre_test' => 'Test vacio',
        'descripcion' => 'Aun no tiene preguntas',
        'id_profesor' => $profesor->id,
    ]);

    $this->actingAs($profesor);

    Livewire::test(AssignTests::class)
        ->set('grupo_id', $grupo->id)
        ->set('test_id', $test->id)
        ->call('assign')
        ->assertSet('errorMessage', __('This test has no available questions.'));

    expect(AsignacionTest::count())->toBe(0);
});

it('permite asignar un test con preguntas', function () {
    $profesor = User::factory()->create();
    $grupo = Grupo::create([
        'nombre_grupo' => 'Grupo con preguntas',
        'id_profesor' => $profesor->id,
    ]);
    $test = Test::create([
        'nombre_test' => 'Test listo',
        'descripcion' => 'Tiene preguntas',
        'id_profesor' => $profesor->id,
    ]);

    Pregunta::create([
        'test_id' => $test->id,
        'tipo_pregunta' => 'preferencia',
        'texto_pregunta' => 'A quien eliges?',
        'orden' => 1,
    ]);

    $this->actingAs($profesor);

    Livewire::test(AssignTests::class)
        ->set('grupo_id', $grupo->id)
        ->set('test_id', $test->id)
        ->call('assign')
        ->assertSet('successMessage', __('Test assigned successfully.'));

    expect(AsignacionTest::count())->toBe(1);
});
